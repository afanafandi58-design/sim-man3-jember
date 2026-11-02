#!/usr/bin/env bash
# Point 9D — ganti adminlte dengan versi production-safe (tanpa NavbarSuccessContrast)
set -e
APP="${APP_ROOT:-$HOME/jurnal-kbm.man3jember.sch.id}"
cd "$APP"
BASE="https://raw.githubusercontent.com/afanafandi58-design/sim-man3-jember/cursor/lms-modul-cd7e/jurnal-kbm"

PHP=""
for bin in ea-php83 php83 /usr/local/bin/ea-php83 php; do
  command -v "$bin" >/dev/null 2>&1 || continue
  ver=$("$bin" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || true)
  case "$ver" in 8.3|8.4|8.5|9.*) PHP="$bin"; break ;; esac
done
[ -n "$PHP" ] || PHP=php

cp -a config/adminlte.php "config/adminlte.php.bak.$(date +%Y%m%d%H%M%S)" 2>/dev/null || true
curl -fsSL "$BASE/config/adminlte.production-safe.php" -o config/adminlte.php
echo "ok adminlte size=$(wc -c < config/adminlte.php) (target 22618)"

echo "=== php -l & include test ==="
"$PHP" -l config/adminlte.php
"$PHP" -r 'include "config/adminlte.php"; echo "ok include_array\n";'

rm -f bootstrap/cache/*.php 2>/dev/null || true
find storage/framework/views -name '*.php' -delete 2>/dev/null || true
"$PHP" artisan optimize:clear 2>&1 | tail -n 5

echo "=== migrate ==="
"$PHP" artisan migrate --force --no-interaction 2>&1 | tail -n 8

echo "Done 9D. Browser: logout → login guru/admin → /lms Ctrl+Shift+R"
