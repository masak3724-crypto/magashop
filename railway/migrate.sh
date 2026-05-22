#!/bin/sh
set -eu

. railway/env-db.sh

echo "[railway] Waiting for PostgreSQL and running migrations..."

if [ -z "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ] && [ -z "${PGHOST:-}" ]; then
  echo "[railway] ERROR: Link PostgreSQL to this service."
  exit 1
fi

case "${DATABASE_URL:-}${DB_URL:-}" in
  *'${{'*|*'${'*)
    echo "[railway] ERROR: DATABASE_URL is unresolved. Link Postgres in Railway."
    exit 1
    ;;
esac

attempt=0
max=12
until php artisan migrate:status --no-interaction >/dev/null 2>&1; do
  attempt=$((attempt + 1))
  if [ "$attempt" -ge "$max" ]; then
    echo "[railway] ERROR: PostgreSQL unreachable:"
    php artisan migrate:status --no-interaction 2>&1 || true
    exit 1
  fi
  echo "[railway] DB not ready (${attempt}/${max})..."
  sleep 2
done

php artisan migrate --force --no-interaction
echo "[railway] Migrations complete."
