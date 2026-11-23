# Backend — Spend Calc

Laravel 13 / PHP 8.4 application that:

1. Polls Bybit card transaction records for each connected user every 5 minutes.
2. Stores transactions in Postgres with amount, currency and USD-equivalent.
3. Pushes a Telegram notification per new expense with an inline keyboard for picking a category.

## Stack

| Component | Details |
|-----------|---------|
| PHP | 8.4-fpm-alpine (Docker) |
| Laravel | 13 |
| Database | PostgreSQL 16 (via `pdo_pgsql`) |
| Cache / Queue / Sessions | Redis 7 (phpredis) |
| Queue runner | Laravel Horizon |
| Telegram bot | [`nutgram/laravel`](https://github.com/nutgram/laravel) (`nutgram/nutgram` v4 core) |
| HTTP server | Nginx → PHP-FPM (see `docker/nginx/default.conf`) |

## Phase 0 — Done

- QR Menu starter code removed (it only lived in the old docs; the actual Laravel app was a vanilla skeleton).
- Docker compose paths fixed (`./laravel` → `./backend`).
- Horizon and Nutgram installed and published (`config/horizon.php`, `config/nutgram.php`, `routes/telegram.php`).
- `routes/api.php` created and wired into `bootstrap/app.php`.
- `backend/.env.example` switched to Postgres + Redis, Telegram and Bybit variables added.

## Entry points

```
POST/GET /api/*   → Laravel API (routes/api.php)
GET      /up      → built-in health check
GET      /horizon → Horizon dashboard
```

Telegram bot handlers live in `routes/telegram.php` and are dispatched via a webhook (set up in Phase 3 — currently only `/start` stub is registered).

## Roadmap (next phases)

1. **Phase 1 — schema.** `users` extended with Telegram/Bybit fields, `categories` (per-user, seeded with a classic set), `transactions` (Bybit-sourced, with fiat equivalent).
2. **Phase 2 — Bybit polling.** `BybitClient` + fetcher + two jobs (`SyncAllUsersBybitTransactions` → `SyncUserBybitTransactions`), scheduled every 5 minutes.
3. **Phase 3 — Telegram bot UX.** `/start`, `/connect`, `/disconnect`, `/categories`, `/report`, `/settings`, inline keyboard for category assignment.
4. **Phase 4 — Mini App API.** `/api/auth/telegram` (initData), `/api/transactions`, `/api/categories`, `/api/reports/summary`. Then build the Vue Mini App in `frontend/`.