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

# Wait for MySQL using a direct PDO ping (no full Laravel bootstrap needed)
echo "[+] Waiting for database connection..."
RETRIES=30
until php -r "
    try {
        \$dsn = 'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE');
        new PDO(\$dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null; do
    echo "    Database not ready — retrying in 2s... ($RETRIES attempts left)"
    RETRIES=$((RETRIES - 1))
    if [ $RETRIES -eq 0 ]; then
        echo "[!] Could not connect to database after multiple retries. Exiting."
        exit 1
    fi
    sleep 2
done

echo "[+] Database is ready."

echo "[+] Clearing bootstrap cache (host may have dev-only package references)..."
rm -f /var/www/bootstrap/cache/packages.php
rm -f /var/www/bootstrap/cache/services.php
rm -f /var/www/bootstrap/cache/config.php
rm -f /var/www/bootstrap/cache/routes*.php
rm -f /var/www/bootstrap/cache/events.php

echo "[+] Regenerating package discovery cache (no dev dependencies)..."
php artisan package:discover --ansi

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
