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
# the *right* `php` available (added to .bash_profile/.bashrc by hPanel's
# PHP version selector) never get loaded. Load them explicitly, then check
# every candidate's actual version -- the system default `php` on PATH is
# often a much older PHP left over from the base OS (seen: 7.2.34) that's
# unrelated to whatever version hPanel has this domain set to, so finding
# *a* php is not enough; it has to satisfy composer.json's ^8.2 requirement.
for profile in ~/.bash_profile ~/.bashrc ~/.profile; do
  [ -f "$profile" ] && source "$profile" 2>/dev/null || true
done

is_php_82_plus() {
  "$1" -r 'exit((float) PHP_VERSION >= 8.2 ? 0 : 1);' >/dev/null 2>&1
}

PHP_BIN=""
for candidate in \
  php8.3 php8.2 php83 php82 \
  /usr/local/bin/php8.3 /usr/local/bin/php8.2 /usr/local/bin/php83 /usr/local/bin/php82 \
  /usr/bin/php8.3 /usr/bin/php8.2 /usr/bin/php83 /usr/bin/php82 \
  /opt/alt/php83/usr/bin/php /opt/alt/php82/usr/bin/php \
  /opt/cpanel/ea-php83/root/usr/bin/php /opt/cpanel/ea-php82/root/usr/bin/php \
  ~/bin/php php /usr/local/bin/php /usr/bin/php; do
  resolved="$candidate"
  case "$candidate" in
    /*|~*) [ -x "$resolved" ] || continue ;;
    *) command -v "$candidate" >/dev/null 2>&1 || continue ;;
  esac
  if is_php_82_plus "$resolved"; then
    PHP_BIN="$resolved"
    break
  fi
done

if [ -z "$PHP_BIN" ]; then
  echo "No PHP 8.2+ CLI binary found on PATH or in common Hostinger locations (the default 'php' on PATH, if any, is an incompatible older version). Storage dirs and .env are set up, but APP_KEY generation and cache clearing were skipped. Find the right binary with 'php -v' / 'which -a php' variants over an interactive SSH session, or check hPanel's PHP configuration for a CLI path, and hardcode it in scripts/remote-setup.sh." >&2
  exit 1
fi

if grep -q '^APP_KEY=$' .env; then
  "$PHP_BIN" artisan key:generate --force
fi

# The very first install's migrations run inside the /install wizard itself
# (no DB is configured yet at this point in a first deploy). Every deploy
# after that needs to pick up new migrations added since install -- run
# them here, but only once the wizard has actually completed, so this never
# races an as-yet-unconfigured database.
if [ -f storage/app/installed.lock ]; then
  "$PHP_BIN" artisan migrate --force
fi

"$PHP_BIN" artisan config:clear
"$PHP_BIN" artisan route:clear
"$PHP_BIN" artisan view:clear
