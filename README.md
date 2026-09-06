# Laravel + Vue Starter

Шаблон проекта на Laravel + Vue 3 с Docker.

## Стек

| | |
|---|---|
| Backend | Laravel, PHP 8.x, PostgreSQL, Redis, Horizon |
| Frontend | Vue 3, Vite, Tailwind CSS v4, Pinia, Vue Router, vue-i18n |
| Инфраструктура | Docker, Nginx |

## Структура

```
backend/    # Laravel
frontend/   # Vue 3
docker/     # Dockerfile'ы и конфиги Nginx
docker-compose.yml
.env.example
```

## Запуск

```bash
cp .env.example .env

# Backend
composer create-project laravel/laravel backend

# Запустить контейнеры
docker compose up -d

# Frontend
cd frontend && npm install && npm run dev
```