#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

if [ "${RUN_COMPOSER_INSTALL:-true}" = "true" ] && [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist --no-progress
fi

if [ "${RUN_NPM_INSTALL:-true}" = "true" ] && { [ ! -d node_modules ] || [ ! -x node_modules/.bin/vite ]; }; then
    if [ -f package-lock.json ]; then
        npm ci --no-audit --no-fund
    else
        npm install --no-audit --no-fund
    fi
fi

if [ ! -f .env ]; then
    echo "Arquivo .env nao encontrado e .env.example nao existe." >&2
    exit 1
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force --no-interaction
fi

if [ "${DB_CONNECTION:-}" = "mysql" ]; then
    until mysqladmin ping \
        -h"${DB_HOST:-database}" \
        -P"${DB_PORT:-3306}" \
        -u"${DB_USERNAME:-betini}" \
        -p"${DB_PASSWORD:-secret}" \
        --silent; do
        sleep 2
    done
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi

if [ "${RUN_NPM_BUILD:-false}" = "true" ]; then
    npm run build
fi

exec "$@"
