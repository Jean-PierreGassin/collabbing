#!/bin/sh
set -eu

envsubst '${TZ}' < /usr/local/etc/php/php.ini.template > /usr/local/etc/php/conf.d/zz-app.ini
envsubst '${XDEBUG_MODE} ${XDEBUG_CLIENT_HOST} ${XDEBUG_CLIENT_PORT}' \
  < /usr/local/etc/php/xdebug.ini.template \
  > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Ensure Laravel writable dirs are owned by www-data
APP_PATH="${APP_CONTAINER_PATH:-/var/www/html}"
if [ -d "$APP_PATH/artisan" ] || [ -f "$APP_PATH/artisan" ]; then
  chown -R www-data:www-data "$APP_PATH/storage" "$APP_PATH/bootstrap/cache" 2>/dev/null || true
  chmod -R ug+rwX "$APP_PATH/storage" "$APP_PATH/bootstrap/cache" 2>/dev/null || true
fi

exec docker-php-entrypoint "$@"
