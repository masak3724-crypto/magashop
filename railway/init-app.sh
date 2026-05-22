#!/bin/sh
set -eu

echo "[railway] Storage link..."
php artisan storage:link --force 2>/dev/null || true
echo "[railway] Done."
