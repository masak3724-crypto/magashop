#!/bin/sh
set -eu

echo "[railway] Post-migrate setup..."

if [ -z "${APP_KEY:-}" ]; then
  echo "[railway] ERROR: Set APP_KEY in Railway Variables."
  exit 1
fi

php artisan storage:link --force 2>/dev/null || true
sh railway/seed.sh

echo "[railway] Done."
