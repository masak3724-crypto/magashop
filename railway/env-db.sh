#!/bin/sh
# Подключение к PostgreSQL на Railway (pre-deploy / runtime)

export DB_CONNECTION="${DB_CONNECTION:-pgsql}"
export PGSSLMODE="${PGSSLMODE:-require}"

# Private URL — предпочтительно внутри сети Railway
if [ -n "${DATABASE_PRIVATE_URL:-}" ] && ! printf '%s' "$DATABASE_PRIVATE_URL" | grep -q '${'; then
  export DATABASE_URL="${DATABASE_PRIVATE_URL}"
fi

# Публичный URL (если private недоступен на pre-deploy)
if [ -z "${DATABASE_URL:-}" ] && [ -n "${DATABASE_PUBLIC_URL:-}" ] && ! printf '%s' "$DATABASE_PUBLIC_URL" | grep -q '${'; then
  export DATABASE_URL="${DATABASE_PUBLIC_URL}"
fi

# Переменные плагина Postgres (PGHOST / POSTGRES_*)
if [ -z "${DATABASE_URL:-}" ] && [ -n "${PGHOST:-}" ]; then
  export PGPORT="${PGPORT:-5432}"
  export PGDATABASE="${PGDATABASE:-${POSTGRES_DB:-railway}}"
  export PGUSER="${PGUSER:-${POSTGRES_USER:-postgres}}"
  export PGPASSWORD="${PGPASSWORD:-${POSTGRES_PASSWORD:-}}"
fi
