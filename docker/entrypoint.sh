#!/usr/bin/env bash
set -e

DB_HOST="${DB_HOST:-mysql}"
DB_PORT="${DB_PORT:-3306}"

echo "Menunggu MySQL di ${DB_HOST}:${DB_PORT}..."
until php -r "try { new PDO('mysql:host=${DB_HOST};port=${DB_PORT}', '${DB_USERNAME}', '${DB_PASSWORD}'); exit(0); } catch (Throwable \$e) { fwrite(STDERR, \$e->getMessage().PHP_EOL); exit(1); }" >/dev/null 2>&1; do
    sleep 2
done

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

php artisan migrate --force
php artisan db:seed --class=AdminSeeder --force || true
php artisan db:seed --class=ReferenceDataSeeder --force || true

if [ "${TECHMIND_DEMO_MODE}" = "true" ]; then
    php artisan techmind:seed-demo || true
fi

exec "$@"
