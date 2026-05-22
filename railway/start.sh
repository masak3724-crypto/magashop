#!/bin/sh
set -eu

PORT="${PORT:-8080}"

echo "[railway] Starting HTTP server on 0.0.0.0:${PORT} (DB init in background)..."

sh railway/clear-cache.sh
php artisan package:discover --no-interaction

sh railway/init-app.sh &

exec php artisan serve --host=0.0.0.0 --port="${PORT}"
