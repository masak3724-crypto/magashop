#!/bin/sh
set -eu

. railway/env-db.sh

echo "[railway] PostgreSQL: migrations + idempotent seed..."

if [ -z "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ] && [ -z "${PGHOST:-}" ]; then
  echo "[railway] ERROR: Link PostgreSQL to this service (DATABASE_URL or PGHOST)."
  exit 1
fi

case "${DATABASE_URL:-}${DB_URL:-}" in
  *'${{'*|*'${'*)
    echo "[railway] ERROR: DATABASE_URL is unresolved. Link Postgres in Railway."
    exit 1
    ;;
esac

if [ -n "${DATABASE_URL:-}" ]; then
  echo "[railway] DB target: DATABASE_URL (sslmode=${PGSSLMODE})"
elif [ -n "${PGHOST:-}" ]; then
  echo "[railway] DB target: ${PGHOST}:${PGPORT:-5432}/${PGDATABASE:-railway}"
fi

attempt=0
max=30
until php railway/check-db.php >/dev/null 2>&1; do
  attempt=$((attempt + 1))
  if [ "$attempt" -ge "$max" ]; then
    echo "[railway] ERROR: PostgreSQL unreachable after ${max} attempts:"
    php railway/check-db.php 2>&1 || true
    exit 1
  fi
  echo "[railway] DB not ready (${attempt}/${max})..."
  sleep 3
done

echo "[railway] PostgreSQL connected."

php artisan migrate --force --no-interaction
echo "[railway] Migrations complete."

php artisan db:seed --class=Database\\Seeders\\RailwaySeeder --force --no-interaction
echo "[railway] RailwaySeeder complete (idempotent)."
