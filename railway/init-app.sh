#!/bin/sh
set -eu

echo "[railway] Laravel init..."

php artisan storage:link --force 2>/dev/null || true
php artisan migrate --force --no-interaction

php artisan db:seed --class=Database\\Seeders\\RailwaySeeder --force --no-interaction

php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

echo "[railway] Done."
