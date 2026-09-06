# Backend — QR Menu (Laravel 13 / PHP 8.4)

## Stack

| Component | Version / Details |
|-----------|------------------|
| PHP | 8.4-fpm-alpine (Docker) |
| Laravel | 13 |
| Database | PostgreSQL (via pdo_pgsql) |
| Cache / Queue | Redis |
| Auth | JWT — `php-open-source-saver/jwt-auth` v2.9, HS256, TTL 60 min, refresh 20160 min |
| HTTP server | Nginx → PHP-FPM (see `docker/nginx/default.conf`) |

## Entry Point

```
POST/GET /api/* → Nginx → php-fpm → laravel/public/index.php
```

`bootstrap/app.php` registers:
- API routes from `routes/api.php`
- `permission` middleware alias → `CheckPermission`
- JSON exception handler for API (all errors return JSON)

---

## Auth Flow

Route prefix: `/api/auth`

| Method | URL | Description |
|--------|-----|-------------|
| POST | `/api/auth/register` | Create user, return `{token}` |
| POST | `/api/auth/login` | Email + password → `{token}` |
| POST | `/api/auth/logout` | Invalidate JWT |
| POST | `/api/auth/refresh` | Rotate JWT token |
| GET | `/api/auth/me` | Returns authenticated user with role and permissions |

`me` response shape:
```json
{
  "id": 1,
  "name": "...",
  "email": "...",
  "restaurant_id": null,
  "role": {
    "slug": "super_admin",
    "permissions": [{ "slug": "restaurants.view" }, ...]
  }
}
```

---

## Roles & Permissions

### Tables

- `roles` (id, name, slug)
- `permissions` (id, name, slug)
- `role_permission` pivot (role_id, permission_id)
- `users.role_id` FK → roles

### Slugs

| Permission | super_admin | admin |
|-----------|:-----------:|:-----:|
| `restaurants.view` | ✓ | — |
| `restaurants.create` | ✓ | — |
| `restaurants.edit` | ✓ | — |
| `restaurants.delete` | ✓ | — |
| `categories.view` | ✓ | ✓ |
| `categories.create` | ✓ | ✓ |
| `categories.edit` | ✓ | ✓ |
| `categories.delete` | ✓ | ✓ |
| `users.view` | ✓ | ✓ |
| `users.edit` | ✓ | ✓ |
| `users.manage` | ✓ | — |
| `roles.manage` | ✓ | — |

Subcategories and menu items reuse `categories.*` permissions — no separate permission slugs needed.

### Middleware

`app/Http/Middleware/CheckPermission.php` — reads `permission:slug` from route, checks `$user->hasPermission($slug)`, returns 403 JSON if denied.

`User::hasPermission(string $slug): bool` — `$this->role->permissions->contains('slug', $slug)`

### Default Seed

Run `php artisan db:seed` to create:
- All 12 permissions
- Roles `super_admin` and `admin` with their respective permission sets
- User `superadmin@qrmenu.com` / `password` with `super_admin` role

---

## Database Schema

```
roles
  id, name, slug, timestamps

permissions
  id, name, slug, timestamps

role_permission
  role_id FK roles, permission_id FK permissions

users
  id, name, email (unique), password, role_id FK roles,
  restaurant_id nullable FK restaurants, remember_token, timestamps

restaurants
  id, name, email (unique), owner_first_name, owner_last_name (nullable),
  country (default 'Italy'), city, address, phone,
  tables_count (unsigned int), registered_at (date), timestamps

categories
  id, restaurant_id FK restaurants (cascade delete),
  name, slug, sort_order (smallint default 0),
  is_active (bool default true), image (nullable), timestamps
  UNIQUE(restaurant_id, slug)

subcategories
  id, category_id FK categories (cascade delete),
  name, slug, sort_order (smallint default 0),
  is_active (bool default true), image (nullable), timestamps
  UNIQUE(category_id, slug)

menu_items
  id, category_id FK categories (cascade delete),
  subcategory_id nullable FK subcategories (set null on delete),
  name, price (decimal 8,2),
  composition (text, nullable), image (nullable),
  sort_order (smallint default 0), is_active (bool default true), timestamps
```

**menu_items** belong directly to a category. `subcategory_id` is optional — a dish can exist in a category without being assigned to any subcategory.

---

## File Storage

Uploaded images are stored via Laravel's `public` disk (`storage/app/public`).

- Categories: `images/categories/<filename>`
- Subcategories: `images/subcategories/<filename>`
- Menu items: `images/menu-items/<filename>`

Nginx serves `/storage/*` directly via `alias /var/www/app/storage/app/public` — no `storage:link` symlink needed.

When updating a resource with a new image, the old file is deleted from disk automatically.

---

## API Routes

All routes below require `Authorization: Bearer <token>` header.

### Restaurants (`permission:restaurants.*`)

| Method | URL | Permission | Description |
|--------|-----|-----------|-------------|
| GET | `/api/restaurants` | `restaurants.view` | Paginated list (15/page), each item includes `owner` (first User) |
| GET | `/api/restaurants/{id}` | `restaurants.view` | Single restaurant with owner |
| POST | `/api/restaurants` | `restaurants.create` | Create restaurant + admin user |
| PUT | `/api/restaurants/{id}` | `restaurants.edit` | Update restaurant |
| DELETE | `/api/restaurants/{id}` | `restaurants.delete` | Delete restaurant |

**POST /api/restaurants** side effect: creates a `User` with the restaurant's `email`, role=`admin`, `restaurant_id`=new restaurant. Returns `{...restaurant, temp_password: "xxxxxxxx"}`.

### Categories (`permission:categories.*`)

| Method | URL | Permission | Description |
|--------|-----|-----------|-------------|
| GET | `/api/categories` | `categories.view` | All categories ordered by sort_order, name |
| GET | `/api/categories/{id}` | `categories.view` | Single category |
| POST | `/api/categories` | `categories.create` | Create category, auto-generates slug. Accepts multipart/form-data for optional image upload |
| PUT | `/api/categories/{id}` | `categories.edit` | Update name / is_active / image |
| PATCH | `/api/categories/reorder` | `categories.edit` | Reorder: `{ids: [3,1,2]}` |
| DELETE | `/api/categories/{id}` | `categories.delete` | Delete, removes image from disk |

**Multi-tenancy in CategoryController:**
- `restaurantId()`: if user has `restaurants.view` → reads `restaurant_id` from request param/body; otherwise uses `$user->restaurant_id`
- `authorizeCategory()`: non-super-admin cannot access categories of another restaurant (403)

### Subcategories (`permission:categories.*`)

| Method | URL | Permission | Description |
|--------|-----|-----------|-------------|
| GET | `/api/subcategories?category_id=X` | `categories.view` | All subcategories for a category, ordered by sort_order, name |
| GET | `/api/subcategories/{id}` | `categories.view` | Single subcategory |
| POST | `/api/subcategories` | `categories.create` | Create subcategory. Requires `category_id`. Accepts multipart/form-data for optional image |
| PUT | `/api/subcategories/{id}` | `categories.edit` | Update name / is_active / image |
| PATCH | `/api/subcategories/reorder` | `categories.edit` | Reorder: `{ids: [...], category_id: X}` |
| DELETE | `/api/subcategories/{id}` | `categories.delete` | Delete, removes image from disk |

Authorization chain: subcategory → category → restaurant ownership check.

### Menu Items (`permission:categories.*`)

| Method | URL | Permission | Description |
|--------|-----|-----------|-------------|
| GET | `/api/menu-items?category_id=X` | `categories.view` | All items for a category, ordered by sort_order, name. Optional `subcategory_id` filter |
| GET | `/api/menu-items/{id}` | `categories.view` | Single menu item |
| POST | `/api/menu-items` | `categories.create` | Create item. Requires `category_id`, optional `subcategory_id`. Accepts multipart/form-data |
| PUT | `/api/menu-items/{id}` | `categories.edit` | Update fields. `subcategory_id` can be set to null to unassign |
| PATCH | `/api/menu-items/reorder` | `categories.edit` | Reorder: `{ids: [...], category_id: X}` |
| DELETE | `/api/menu-items/{id}` | `categories.delete` | Delete, removes image from disk |

Authorization: item → category → restaurant ownership check (direct, no subcategory hop).

### Users (`permission:users.*`)

| Method | URL | Permission | Description |
|--------|-----|-----------|-------------|
| GET | `/api/users` | `users.view` | Paginated list; admin sees only own restaurant |
| GET | `/api/users/{id}` | `users.view` | Single user with role |
| POST | `/api/users` | `users.manage` | Create user |
| PUT | `/api/users/{id}` | `users.edit` | Update user, password optional |
| DELETE | `/api/users/{id}` | `users.manage` | Delete user |

### Public Menu (no auth)

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/api/public/{subdomain}` | Full menu for a restaurant by subdomain. Returns 404 if subdomain not found or not set |

Response shape:
```json
{
  "restaurant": { "id", "name", "city", "address", "phone", "subdomain" },
  "categories": [
    {
      "id", "name", "image",
      "items": [ { "id", "name", "price", "composition", "image" } ],
      "subcategories": [
        {
          "id", "name", "image",
          "items": [ { "id", "name", "price", "composition", "image" } ]
        }
      ]
    }
  ]
}
```
Only `is_active = true` categories, subcategories, and items are included. Items in `category.items` have no subcategory (`subcategory_id IS NULL`). Items in `subcategory.items` belong to that subcategory.

### Restaurant Settings (auth, no permission guard)

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/api/restaurant/settings` | Returns the current restaurant. Admin: own restaurant; super_admin: uses `restaurant_id` query param |
| PUT | `/api/restaurant/settings` | Updates restaurant fields (all except `email`). Same resolution logic |

### Other

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/api/roles` | Returns all roles (used in user forms) |

---

## Models

### `User` (`app/Models/User.php`)
- Implements `JWTSubject`
- Fillable: `name`, `email`, `password`, `role_id`, `restaurant_id`
- Hidden: `password`, `remember_token`
- Relations: `belongsTo(Role)`, `belongsTo(Restaurant)`
- `hasPermission(string $slug): bool`

### `Restaurant` (`app/Models/Restaurant.php`)
- Fillable: all fields except id/timestamps
- Relations: `hasMany(User)`, `hasOne(User as 'owner')`

### `Category` (`app/Models/Category.php`)
- Fillable: `restaurant_id`, `name`, `slug`, `sort_order`, `is_active`, `image`
- Relations: `belongsTo(Restaurant)`, `hasMany(Subcategory)`
- `static generateSlug(string $name, int $restaurantId, ?int $ignoreId = null): string`

### `Subcategory` (`app/Models/Subcategory.php`)
- Fillable: `category_id`, `name`, `slug`, `sort_order`, `is_active`, `image`
- Relations: `belongsTo(Category)`, `hasMany(MenuItem)`
- `static generateSlug(string $name, int $categoryId, ?int $ignoreId = null): string` — same pattern as Category, scoped to category_id

### `MenuItem` (`app/Models/MenuItem.php`)
- Fillable: `category_id`, `subcategory_id`, `name`, `price`, `composition`, `image`, `sort_order`, `is_active`
- Casts: `is_active` → boolean, `sort_order` → integer, `price` → decimal:2
- Relations: `belongsTo(Category)`, `belongsTo(Subcategory)`

### `Role` (`app/Models/Role.php`)
- Relations: `belongsToMany(Permission)`, `hasMany(User)`

### `Permission` (`app/Models/Permission.php`)
- Relations: `belongsToMany(Role)`

---

## Form Requests (Validation)

| File | Route |
|------|-------|
| `StoreRestaurantRequest` | POST /restaurants |
| `UpdateRestaurantRequest` | PUT /restaurants/{id} — email unique ignoring self |
| `StoreCategoryRequest` | POST /categories — name required, restaurant_id nullable exists, image nullable file |
| `UpdateCategoryRequest` | PUT /categories/{id} — all fields optional, image nullable file |
| `StoreSubcategoryRequest` | POST /subcategories — name required, category_id nullable exists, image nullable file |
| `UpdateSubcategoryRequest` | PUT /subcategories/{id} — all fields optional, image nullable file |
| `StoreMenuItemRequest` | POST /menu-items — name/price/category_id required, subcategory_id nullable exists, image nullable file |
| `UpdateMenuItemRequest` | PUT /menu-items/{id} — all fields optional, subcategory_id nullable exists, image nullable file |
| `StoreUserRequest` | POST /users |
| `UpdateUserRequest` | PUT /users/{id} — password nullable |

---

## Docker Setup

Container names use `${COMPOSE_PREFIX}` env var (defined in `.env` at project root).

| Container | Image | Role |
|-----------|-------|------|
| `${COMPOSE_PREFIX}-php` | `docker/php/Dockerfile` (php:8.4-fpm-alpine) | Laravel PHP-FPM |
| `${COMPOSE_PREFIX}-nginx` | nginx:alpine | Reverse proxy |
| `${COMPOSE_PREFIX}-pgsql` | postgres:16 | Database |
| `${COMPOSE_PREFIX}-redis` | redis:alpine | Cache & queue |
| `${COMPOSE_PREFIX}-frontend` | `frontend/Dockerfile` (node:20-alpine) | Vite dev server |

Volume: `${COMPOSE_PREFIX}_pg_data` (named, interpolated via `name:` key in volumes section).

Common commands:
```bash
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed
docker compose exec php php artisan jwt:secret
docker compose exec frontend npm install <pkg>
```