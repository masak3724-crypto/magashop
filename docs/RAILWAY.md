# Деплой ModaStyle на Railway (PostgreSQL)

Проект настроен на **PostgreSQL** в продакшене. SQLite на Railway не используется — данные теряются при пересборке.

## 1. Создать проект

1. [railway.app/new](https://railway.app/new) → **Deploy from GitHub repo**
2. Репозиторий: `masak3724-crypto/magashop`, ветка `main`

## 2. PostgreSQL

1. На canvas проекта: **+ New** → **Database** → **PostgreSQL**
2. Имя сервиса по умолчанию: `Postgres` (если переименуете — обновите переменные в шаге 3)
3. Откройте сервис Laravel → **Settings** → **Connect** (или перетащите стрелку Postgres → Web) — Railway подставит `DATABASE_URL`

## 3. Переменные Laravel-сервиса

**Variables** → **Raw Editor** — вставьте [`.env.railway`](../.env.railway).

Обязательно:

| Переменная | Значение |
|------------|----------|
| `APP_KEY` | `php artisan key:generate --show` (локально) |
| `DATABASE_URL` | `${{Postgres.DATABASE_URL}}` (из шаблона) |
| `DB_CONNECTION` | `pgsql` |

После **Networking** → **Generate Domain** Railway задаёт `RAILWAY_PUBLIC_DOMAIN` — приложение само выставит HTTPS URL (не добавляйте `APP_URL=${{RAILWAY_PUBLIC_DOMAIN}}`, это ломает `composer install` при сборке).

## 4. Деплой

Перед запуском контейнера (**pre-deploy**): `railway/predeploy.sh` — миграции и идемпотентный `RailwaySeeder`. При **старте** — только HTTP (`railway/start.sh`).

1. Проверка `APP_KEY` и подключения PostgreSQL
2. `migrate --force`
3. `RailwaySeeder` — идемпотентно: каталог по `slug` (`wb-{nm}`) + демо-пользователи
4. `storage:link`

Healthcheck: `GET /up` (см. `railway.json`).

Обязательные переменные: `APP_KEY`, `DATABASE_URL` (или связь с Postgres). Без `APP_KEY` pre-deploy завершится с ошибкой.

## 5. Локальная разработка

| Окружение | БД |
|-----------|-----|
| Локально (по умолчанию) | SQLite — `.env.example` |
| Railway / прод | PostgreSQL — `.env.railway` |

Локально с PostgreSQL (Docker):

```bash
docker run -d --name modastyle-pg -e POSTGRES_PASSWORD=secret -e POSTGRES_DB=modastyle -p 5432:5432 postgres:16
```

В `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=modastyle
DB_USERNAME=postgres
DB_PASSWORD=secret
```

## 6. Демо-аккаунты (после первого seed)

| Роль | Email | Пароль |
|------|-------|--------|
| Админ | admin@modastyle.ru | password |
| Покупатель | demo@modastyle.ru | password |

Смените пароли в продакшене.

## 7. CLI

```bash
npm i -g @railway/cli
railway login
railway link
railway run php artisan migrate:status
railway run php artisan catalog:sync --prune
```

## Файлы конфигурации

| Файл | Назначение |
|------|------------|
| `nixpacks.toml` | PHP 8.4, `pdo_pgsql`, GD |
| `railway.json` | preDeploy, healthcheck `/up` |
| `railway/predeploy.sh` | migrate + RailwaySeeder + storage:link |
| `railway/migrate.sh` | ожидание Postgres, migrate, seed |
| `railway/start.sh` | HTTP-сервер |
| `.env.railway` | шаблон переменных (только Postgres) |
| `app/Support/RailwayPostgres.php` | URL, SSL, HTTPS на Railway |
