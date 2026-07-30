#!/bin/bash
# Run on the Hostinger server (via SSH) after every deploy. Idempotent —
# safe to run on the very first deploy and every one after.
set -e

cd "$1"

mkdir -p storage/framework/cache/data storage/framework/sessions \
         storage/framework/testing storage/framework/views \
         storage/logs storage/app/public storage/app/private uploads
chmod -R 775 storage bootstrap/cache uploads

# First deploy only: bootstrap .env from the committed stub and generate a
# fresh per-site APP_KEY. Never touched again after this.
if [ ! -f .env ]; then
  cp .env.production.example .env
fi
if grep -q '^APP_KEY=$' .env; then
  php artisan key:generate --force
fi

php artisan config:clear
php artisan route:clear
php artisan view:clear
