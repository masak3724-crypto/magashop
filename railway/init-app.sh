#!/bin/sh
set -eu

echo "[railway] Laravel init (PostgreSQL)..."

export DB_CONNECTION="${DB_CONNECTION:-pgsql}"

if [ -z "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ] && [ -z "${PGHOST:-}" ]; then
  echo "[railway] ERROR: DATABASE_URL or PGHOST not set. Add PostgreSQL and link it to this service."
  exit 1
fi

attempt=0
max=20
until php artisan db:show --no-interaction 2>/dev/null; do
  attempt=$((attempt + 1))
  if [ "$attempt" -ge "$max" ]; then
    echo "[railway] ERROR: PostgreSQL is not reachable after ${max} attempts."
    exit 1
  fi
  echo "[railway] Waiting for PostgreSQL (${attempt}/${max})..."
  sleep 3
done

php artisan storage:link --force 2>/dev/null || true
php artisan migrate --force --no-interaction
php artisan db:seed --class=Database\\Seeders\\RailwaySeeder --force --no-interaction

php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

echo "[railway] Done."
