#!/bin/sh
set -e

echo "[entrypoint] waiting for postgres at ${DB_HOST}:${DB_PORT:-5432}…"
until pg_isready -h "$DB_HOST" -p "${DB_PORT:-5432}" -U "$DB_USERNAME" -q; do
    sleep 1
done
echo "[entrypoint] postgres is ready."

cd /var/www/html

echo "[entrypoint] running migrations…"
php artisan migrate --force

echo "[entrypoint] caching config, routes, views…"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# storage:link only matters when serving files from the local/public disk
if [ "$FILESYSTEM_DISK" != "gcs" ]; then
    echo "[entrypoint] linking storage…"
    php artisan storage:link --force
fi

echo "[entrypoint] fixing permissions…"
chown -R www-data:www-data storage bootstrap/cache

echo "[entrypoint] starting supervisor…"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
