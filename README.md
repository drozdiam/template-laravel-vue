# Spend Calc

Telegram bot (will later grow into a Mini App) for tracking personal spending.
Polls Bybit card transactions, notifies the user in Telegram and asks to pick a
category for each new expense.

## Stack

| Layer | Details |
|-------|---------|
| Backend | Laravel 13, PHP 8.4, PostgreSQL 16, Redis 7, Horizon |
| Telegram | [nutgram/laravel](https://github.com/nutgram/laravel) |
| Frontend | Vue 3 + Vite + Tailwind v4 (reserved for future Mini App, not developed in MVP) |
| Infrastructure | Docker, Nginx |

## Project layout

```
backend/     # Laravel app
frontend/    # Vue 3 (placeholder for future Mini App)
docker/      # Dockerfiles + Nginx config
docker-compose.yml
.env.example # Only COMPOSE_PREFIX and DB creds — Laravel env lives in backend/.env
```

## Getting started

```bash
cp .env.example .env
cp backend/.env.example backend/.env

docker compose up -d
docker compose exec php composer install
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate
```

Fill in `backend/.env`:

- `TELEGRAM_TOKEN` — Telegram bot token from @BotFather
- `BYBIT_API_BASE_URL` — defaults to `https://api.bybit.com`

Bybit API keys are not global — each user connects their own via `/connect` in the bot.

## Available services

| URL | Purpose |
|-----|---------|
| `http://localhost:8000/up` | Laravel health check |
| `http://localhost:8000/api/health` | API health check |
| `http://localhost:8000/horizon` | Horizon queue dashboard |
| `http://localhost:5173` | Vite dev server (frontend placeholder) |