#!/bin/sh
set -eu

. railway/env-db.sh

echo "[railway] Idempotent seed (RailwaySeeder)..."
php artisan db:seed --class=Database\\Seeders\\RailwaySeeder --force --no-interaction
echo "[railway] Seed complete."
