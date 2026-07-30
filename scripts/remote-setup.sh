#!/bin/bash
# Run on the Hostinger server (via SSH) after every deploy. Idempotent —
# safe to run on the very first deploy and every one after.
set -e

cd "$1"

mkdir -p storage/framework/cache/data storage/framework/sessions \
         storage/framework/testing storage/framework/views \
         storage/logs storage/app/public storage/app/private uploads
chmod -R 775 storage bootstrap/cache uploads

# First deploy only: bootstrap .env from the committed stub. Never touched
# again after this, so it's safe even if the php-dependent steps below fail.
if [ ! -f .env ]; then
  cp .env.production.example .env
fi

# `ssh host "cmd"` runs a non-login shell, so the PATH entries that make
# `php` available (added to .bash_profile/.bashrc by Hostinger's CloudLinux
# PHP selector) never get loaded. Load them explicitly, then fall back to
# common CloudLinux alt-php locations if `php` still isn't found.
for profile in ~/.bash_profile ~/.bashrc ~/.profile; do
  [ -f "$profile" ] && source "$profile" 2>/dev/null || true
done

PHP_BIN=""
if command -v php >/dev/null 2>&1; then
  PHP_BIN="php"
else
  for candidate in /usr/local/bin/php /usr/bin/php ~/bin/php /opt/alt/php*/usr/bin/php; do
    if [ -x "$candidate" ]; then
      PHP_BIN="$candidate"
      break
    fi
  done
fi

if [ -z "$PHP_BIN" ]; then
  echo "No php CLI binary found on PATH or in common Hostinger locations. Storage dirs and .env are set up, but APP_KEY generation and cache clearing were skipped. Find yours with 'which php' over an interactive SSH session and hardcode it in scripts/remote-setup.sh." >&2
  exit 1
fi

if grep -q '^APP_KEY=$' .env; then
  "$PHP_BIN" artisan key:generate --force
fi

"$PHP_BIN" artisan config:clear
"$PHP_BIN" artisan route:clear
"$PHP_BIN" artisan view:clear
