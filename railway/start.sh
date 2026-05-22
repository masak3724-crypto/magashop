#!/bin/sh
set -eu

PORT="${PORT:-8080}"

echo "[railway] Starting HTTP on 0.0.0.0:${PORT}..."

chmod -R u+w storage bootstrap/cache 2>/dev/null || true

exec php artisan serve --host=0.0.0.0 --port="${PORT}"
