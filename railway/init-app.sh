#!/bin/sh
set -eu

echo "[railway] Laravel init (PostgreSQL)..."

if [ -z "${APP_KEY:-}" ]; then
  echo "[railway] ERROR: APP_KEY is not set. Run locally: php artisan key:generate --show"
  echo "[railway] Add the value to Railway → Variables → APP_KEY"
  exit 1
fi

export DB_CONNECTION="${DB_CONNECTION:-pgsql}"

if [ -n "${DATABASE_PRIVATE_URL:-}" ]; then
  export DATABASE_URL="${DATABASE_PRIVATE_URL}"
fi

if [ -z "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ] && [ -z "${PGHOST:-}" ]; then
  echo "[railway] ERROR: Link PostgreSQL to this service (DATABASE_URL or PGHOST missing)."
  exit 1
fi

case "${DATABASE_URL:-}${DB_URL:-}" in
  *'${{'*|*'${'*)
    echo "[railway] ERROR: DATABASE_URL contains unresolved Railway template. Link Postgres service."
    exit 1
    ;;
esac

attempt=0
max=15
until php artisan migrate:status --no-interaction >/dev/null 2>&1; do
  attempt=$((attempt + 1))
  if [ "$attempt" -ge "$max" ]; then
    echo "[railway] ERROR: Cannot connect to PostgreSQL:"
    php artisan migrate:status --no-interaction 2>&1 || true
    exit 1
  fi
  echo "[railway] Waiting for PostgreSQL (${attempt}/${max})..."
  sleep 2
done

php artisan package:discover --no-interaction
php artisan storage:link --force 2>/dev/null || true
php artisan migrate --force --no-interaction
php artisan db:seed --class=Database\\Seeders\\RailwaySeeder --force --no-interaction

echo "[railway] Done."
