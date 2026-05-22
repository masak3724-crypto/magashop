#!/bin/sh
# Удаляет кэш Laravel, собранный с dev-зависимостями (Pail, Collision и т.д.)
rm -f bootstrap/cache/packages.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php
rm -f bootstrap/cache/routes-*.php
rm -f bootstrap/cache/events.php
