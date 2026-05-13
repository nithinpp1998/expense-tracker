#!/bin/bash
set -e

echo "──────────────────────────────────────────"
echo " Expense Tracker — Container startup"
echo "──────────────────────────────────────────"

# Generate APP_KEY if not already set
if [ -z "$APP_KEY" ]; then
    echo "[+] Generating application key..."
    php artisan key:generate --force
fi

# Wait for MySQL to be ready
echo "[+] Waiting for database connection..."
RETRIES=30
until php artisan db:show --no-interaction > /dev/null 2>&1 || [ $RETRIES -eq 0 ]; do
    echo "    Database not ready — retrying in 2s... ($RETRIES attempts left)"
    RETRIES=$((RETRIES - 1))
    sleep 2
done

if [ $RETRIES -eq 0 ]; then
    echo "[!] Could not connect to database after multiple retries. Exiting."
    exit 1
fi

echo "[+] Running migrations..."
php artisan migrate --force --no-interaction

# Seed only in local/development environments
if [ "$APP_ENV" = "local" ] || [ "$APP_ENV" = "development" ]; then
    echo "[+] Seeding database (${APP_ENV} environment)..."
    php artisan db:seed --force --no-interaction 2>/dev/null || true
fi

# Cache in production
if [ "$APP_ENV" = "production" ]; then
    echo "[+] Caching config, routes, and views for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

echo "[+] Setting storage permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "[✓] Startup complete. Starting PHP-FPM..."
echo "──────────────────────────────────────────"

exec "$@"
