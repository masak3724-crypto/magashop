#!/bin/sh
export DB_CONNECTION="${DB_CONNECTION:-pgsql}"

if [ -n "${DATABASE_PRIVATE_URL:-}" ]; then
  export DATABASE_URL="${DATABASE_PRIVATE_URL}"
fi
