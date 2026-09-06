# Frontend — QR Menu (Vue 3 + Vite)

## Stack

| Component | Version / Details |
|-----------|------------------|
| Vue | 3, Composition API (`<script setup>`) |
| Vite | 6 |
| Routing | Vue Router 4 |
| State | Pinia |
| HTTP | Axios |
| Styles | Tailwind CSS v4 — no `@apply` in scoped `<style>` blocks (breaks in v4), use inline classes |
| i18n | vue-i18n v11, `legacy: false` |
| Drag-and-drop | vuedraggable@next (v4, wraps SortableJS) |
| Dev server | `http://localhost:5173` (exposed via Nginx at root) |

---

## Project Structure

```
frontend/src/
├── main.js               # App bootstrap: Vue + Pinia + Router + i18n
├── App.vue               # Root — just <RouterView />
├── i18n.js               # vue-i18n config, loads en.json
├── api/
│   └── index.js          # Axios instance, base URL /api, adds Bearer token
├── stores/
│   ├── auth.js           # User state, JWT token, fetchUser / login / logout
│   └── restaurant.js     # Active restaurant context (enter / leave / restore)
├── router/
│   └── index.js          # Routes + navigation guards
├── layouts/
│   ├── AppLayout.vue     # Authenticated shell (sidebar + topbar + <RouterView>)
│   └── GuestLayout.vue   # Centered card layout for login/register
├── components/
│   ├── Sidebar.vue       # Collapsible nav (two modes: global / restaurant context)
│   └── SidebarItem.vue   # Single nav link with icon slot
├── pages/
│   ├── Dashboard.vue
│   ├── Login.vue
│   ├── Register.vue
│   ├── restaurants/
│   │   ├── Index.vue     # List + "Enter" to set restaurant context
│   │   ├── Create.vue    # Form — shows temp_password on success
│   │   └── Edit.vue      # Pre-filled update form
│   ├── categories/
│   │   ├── Index.vue     # Draggable table, toggle active, PATCH reorder, image upload
│   │   ├── Create.vue    # Name + is_active toggle + optional image
│   │   └── Edit.vue      # Same form, pre-filled, shows current image
│   ├── subcategories/
│   │   ├── Index.vue     # Draggable table for subcategories of a category
│   │   ├── Create.vue    # Name + is_active + optional image
│   │   └── Edit.vue      # Pre-filled, shows current image
│   ├── menu-items/
│   │   ├── Index.vue     # Draggable table for items of a category
│   │   ├── Create.vue    # Name + price + composition + subcategory selector + image
│   │   └── Edit.vue      # Same form, pre-filled
│   └── users/
│       ├── Index.vue     # User table, paginated
│       ├── Create.vue
│       ├── Edit.vue
│       └── UserForm.vue  # Shared form component
└── locales/
    └── en.json           # All UI strings
```

---

## Routing (`src/router/index.js`)

All routes under `AppLayout` require authentication. Unauthenticated users are redirected to `/login`.

```
/                                             → Dashboard
/login                                        → Login (GuestLayout)
/register                                     → Register (GuestLayout)
/restaurants                                  → restaurants/Index
/restaurants/create                           → restaurants/Create
/restaurants/:id/edit                         → restaurants/Edit
/categories                                   → categories/Index
/categories/create                            → categories/Create
/categories/:id/edit                          → categories/Edit
/categories/:categoryId/subcategories         → subcategories/Index
/categories/:categoryId/subcategories/create  → subcategories/Create
/subcategories/:id/edit                       → subcategories/Edit
/categories/:categoryId/items                 → menu-items/Index
/categories/:categoryId/items/create          → menu-items/Create
/menu-items/:id/edit                          → menu-items/Edit
/users                                        → users/Index
/users/create                                 → users/Create
/users/:id/edit                               → users/Edit
```

**Navigation guard** (`router.beforeEach`):
1. If route requires auth and `auth.user` is null → calls `auth.fetchUser()` (loads from `/api/auth/me`)
2. If still not authenticated → redirect `/login`
3. If route requires guest and user is logged in → redirect `/`

---

## State Management

### `stores/auth.js`

```js
state: { user: null, checked: false }

fetchUser()   // GET /api/auth/me → sets user, calls restaurantStore.restore()
login(email, password)   // POST /api/auth/login → saves token to localStorage
register(name, email, password)
logout()      // POST /api/auth/logout → clears token + restaurantStore.leave()
```

Token is stored in `localStorage` under key `token`.  
After `fetchUser`, `restaurantStore.restore()` is called (dynamic import to avoid circular dep).

### `stores/restaurant.js`

Manages which restaurant is currently "active" (the context for categories/users).

```js
state: { current: null }   // Restaurant object or null

enter(restaurant)  // sets current, saves to sessionStorage (super_admin only)
leave()            // clears current, removes from sessionStorage
restore()          // on app load:
                   //   admin → current = user.restaurant (from auth.user)
                   //   super_admin → restore from sessionStorage if saved
```

`sessionStorage` is used so context survives F5 but clears when the tab is closed.

---

## API Layer (`src/api/index.js`)

Axios instance:
- `baseURL: '/api'`
- Request interceptor: adds `Authorization: Bearer <token>` from `localStorage.getItem('token')`

Usage: `import api from '@/api'` then `api.get(...)`, `api.post(...)`, etc.

**File uploads:** use `FormData` instead of a plain object. For PUT requests, pass `_method: 'PUT'` in the FormData body and call `api.post(url, fd)` — Laravel handles method spoofing transparently.

---

## Layouts

### `AppLayout.vue`

Full-height flex layout with fixed background image.

- **Desktop (md+):** sidebar visible, collapsible (w-64 ↔ w-16 icons-only) via `collapsed` ref
- **Mobile (<md):** sidebar hidden by default; burger button opens a drawer overlay (`Transition` fade + slide)
- **Topbar:** burger icon (mobile), collapse toggle (desktop), user display name, logout button

### `GuestLayout.vue`

Centered card on top of background image. Used for login and register pages.

---

## Components

### `Sidebar.vue`

Adapts based on `restaurantStore.current`:

**Global mode** (no restaurant context — super_admin browsing):
- Dashboard
- Restaurants (requires `restaurants.view`)
- Users (requires `users.view`)

**Restaurant context mode** (super_admin entered a restaurant, or any admin):
- Shows current restaurant name badge
- Categories
- Users (requires `users.view`)
- "← All Restaurants" button (requires `restaurants.view`, only super_admin has this)

`can(slug)` helper: `auth.user?.role?.permissions?.some(p => p.slug === slug)`

### `SidebarItem.vue`

`<RouterLink>` wrapper with icon slot. Hides text label when `collapsed` prop is true.

---

## Subdomain Detection & Public Menu

`src/utils/subdomain.js` exports `getSubdomain()` which reads `window.location.hostname` and returns the subdomain string or `null`:
- `localhost` → `null` (admin app)
- `my-restaurant.localhost` → `"my-restaurant"` (public menu)
- `my-restaurant.domain.com` → `"my-restaurant"` (public menu)
- `domain.com` or `www.domain.com` → `null` (admin app)

`App.vue` calls `getSubdomain()` at startup. If a subdomain is detected, it renders `pages/public/Menu.vue` instead of `RouterView`. The admin router is never loaded on public subdomain requests.

**Public menu pages** (`src/pages/public/`):
- `Menu.vue` — fetches `GET /api/public/{subdomain}`, shows restaurant header + category tabs + items grouped by subcategory
- `MenuItem.vue` — single item card (image, name, composition, price)

**DNS requirement for production:** add a wildcard `A` record `*.yourdomain.com → server IP`. Nginx's catch-all server block handles the rest without changes.

**Local development:** add entries to `/etc/hosts`, e.g. `127.0.0.1 my-restaurant.localhost`, then open `http://my-restaurant.localhost`.

---

## Pages

### Restaurants

**Index.vue**
- Fetches `GET /api/restaurants`
- "Enter" button → `restaurantStore.enter(r)` → navigate to `/categories`
- Delete with confirm dialog

**Create.vue**
- On success, API returns `temp_password` — displayed in a success state (not navigated away)

**Edit.vue**
- Loads existing data on mount, PUT on submit

### Categories

**Index.vue**
- Loads `GET /api/categories?restaurant_id=<id>` (restaurant_id from store)
- `vuedraggable` wraps `<tbody>`, `handle=".drag-handle"` (the `⠿` grip icon)
- On drag end → `PATCH /api/categories/reorder` with `{ ids: [...] }` (ordered array of IDs)
- Toggle `is_active` inline → `PUT /api/categories/{id}` with `{ is_active: !current }`
- **Items** button (green) → `/categories/:id/items`
- **Subcategories** button (indigo) → `/categories/:id/subcategories`

**Create.vue / Edit.vue**
- Fields: Name, Active toggle, optional Image (file input)
- Submits as `FormData` (POST for create, POST with `_method=PUT` for update)
- Edit page shows current image thumbnail if one exists
- `sort_order` is managed server-side via reorder endpoint, not shown in forms

### Subcategories

**Index.vue**
- Loads category name + subcategories for `category_id` (route param `categoryId`)
- Same drag-to-reorder, toggle active pattern as categories
- Back button → `/categories`
- **Items** button per row → `/subcategories/:id/items` (not used — subcategory management only)

**Create.vue / Edit.vue**
- Fields: Name, Active toggle, optional Image
- Edit page loads `category_id` from the subcategory record for back-navigation

### Menu Items

**Index.vue**
- Loads `GET /api/menu-items?category_id=<id>` (route param `categoryId`)
- Shows dish name (with thumbnail if image exists) and truncated composition
- Drag-to-reorder, toggle active
- Back button → `/categories`

**Create.vue / Edit.vue**
- Fields: Name (required), Price (required, decimal), Composition (optional textarea), Active toggle, optional Image
- **Subcategory selector:** dropdown of existing subcategories for the category + `+` button to create a new subcategory inline without leaving the form. Creating inline calls `POST /api/subcategories`, appends the result to the dropdown and auto-selects it
- Submits as `FormData`

### Users

**Index.vue** — table with pagination, prevents self-delete

**UserForm.vue** — shared form (name, email, password, role selector, restaurant selector for super_admin)

---

## i18n (`src/locales/en.json`)

Namespaces:

| Namespace | Usage |
|-----------|-------|
| `auth.*` | Login, register, logout labels |
| `nav.*` | Sidebar links (dashboard, restaurants, categories, users, all_restaurants) |
| `dashboard.*` | Dashboard page |
| `restaurants.*` | All restaurant CRUD labels including `enter`, `temp_password` |
| `categories.*` | Category CRUD labels + `items`, `subcategories` action button labels |
| `subcategories.*` | Subcategory CRUD labels |
| `menu_items.*` | Menu item CRUD labels including `subcategory`, `no_subcategory`, `new_subcategory_placeholder` |
| `users.*` | User CRUD labels |
| `common.*` | loading, error, back, etc. |

Usage in components: `const { t } = useI18n()` → `{{ t('categories.title') }}`

---

## Tailwind Notes

Tailwind CSS v4 is used. **Important constraint:** `@apply` does not work inside Vue `<style scoped>` blocks with Tailwind v4. Always use utility classes directly in templates.

---

## Vite Config (`vite.config.js`)

- Plugin: `@vitejs/plugin-vue`
- Plugin: `@tailwindcss/vite`
- Alias: `@` → `src/`
- Dev server: `host: '0.0.0.0'`, port `5173`
- Proxy: `/api` → `http://nginx` (internal Docker network)

---

## Docker

Frontend runs in `${COMPOSE_PREFIX}-frontend` container (node:20-alpine).  
Source is mounted as a volume so edits on the host are reflected live (HMR via Vite).

To install npm packages **always run inside the container**:
```bash
docker compose exec frontend npm install <package>
```
Installing on the host won't work — the container has its own `node_modules`.