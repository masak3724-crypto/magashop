#!/bin/sh
set -eu

. railway/env-db.sh

echo "[railway] Seeding catalog (if empty)..."
php artisan db:seed --class=Database\\Seeders\\RailwaySeeder --force --no-interaction
echo "[railway] Seed complete."
