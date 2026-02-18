#!/bin/sh

set -e

echo "Waiting for Postgres..."

until pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME
do
  sleep 2
done

echo "Database ready"

if [ ! -d "vendor" ]; then
  echo "Installing composer dependencies..."
  composer install --no-interaction --prefer-dist
fi

if [ ! -f ".env" ]; then
  echo ".env not found, copying from .env.example..."
  cp .env.example .env
fi

echo "Generating app key..."
php artisan key:generate --force

echo "Fixing permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
chmod -R 777 storage/logs

echo "Running migrations..."
php artisan migrate --force

echo "Running seeders..."
php artisan db:seed --force

echo "Fresh seeders..."
php artisan migrate:fresh --seed --force

echo "Starting PHP-FPM..."
exec php-fpm
