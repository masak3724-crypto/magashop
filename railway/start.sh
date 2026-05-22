#!/bin/sh
set -eu

PORT="${PORT:-8080}"

echo "[railway] Starting HTTP server on 0.0.0.0:${PORT} (DB init in background)..."

php artisan package:discover --no-interaction 2>/dev/null || true

sh railway/init-app.sh &

exec php artisan serve --host=0.0.0.0 --port="${PORT}"
