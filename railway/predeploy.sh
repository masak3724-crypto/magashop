#!/bin/sh
set -eu

echo "[railway] Pre-deploy: migrate + seed..."

if [ -z "${APP_KEY:-}" ]; then
  echo "[railway] ERROR: APP_KEY is not set in Railway Variables."
  exit 1
fi

sh railway/clear-cache.sh
php artisan package:discover --no-interaction || {
  echo "[railway] package:discover failed"
  exit 1
}

sh railway/migrate.sh || {
  echo "[railway] migrate/seed failed"
  exit 1
}

php artisan storage:link --force 2>/dev/null || true

echo "[railway] Pre-deploy complete."
