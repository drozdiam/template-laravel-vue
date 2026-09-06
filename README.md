# Laravel + Vue Starter

A starter template built on Laravel + Vue 3 with Docker.

## Stack

| | |
|---|---|
| Backend | Laravel, PHP 8.x, PostgreSQL, Redis, Horizon |
| Frontend | Vue 3, Vite, Tailwind CSS v4, Pinia, Vue Router, vue-i18n |
| Infrastructure | Docker, Nginx |

## Structure

```
backend/    # Laravel
frontend/   # Vue 3
docker/     # Dockerfiles and Nginx config
docker-compose.yml
.env.example
```

## Getting started

```bash
cp .env.example .env

# Backend
cd backend && composer install

# Start containers
docker compose up -d

# Frontend
cd frontend && npm install && npm run dev
```