#!/bin/sh
set -eu

PORT="${PORT:-8080}"

echo "[railway] Boot (port ${PORT})..."

chmod -R u+w storage bootstrap/cache 2>/dev/null || true

sh railway/clear-cache.sh
php artisan package:discover --no-interaction

# HTTP сразу — healthcheck /up; миграции и seed в фоне
(
  sh railway/migrate.sh
  sh railway/init-app.sh
) &

exec php artisan serve --host=0.0.0.0 --port="${PORT}"
