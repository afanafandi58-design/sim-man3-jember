#!/usr/bin/env bash
# Mode jadwal terbuka (guru edit langsung) + log admin + unlock semua jadwal
set -e
APP="${APP_ROOT:-$HOME/jurnal-kbm.man3jember.sch.id}"
cd "$APP"
[ -f artisan ] || { echo "ERROR: artisan missing"; exit 1; }

PHP=""
for bin in ea-php83 php83 /usr/local/bin/ea-php83 php; do
  command -v "$bin" >/dev/null 2>&1 || continue
  ver=$("$bin" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || true)
  case "$ver" in 8.3|8.4|8.5|9.*) PHP="$bin"; break ;; esac
done
[ -n "$PHP" ] || PHP=php

echo "=== migrate (jadwal_change_logs + unlock) ==="
"$PHP" artisan migrate --force --no-interaction 2>&1 | tail -n 15

rm -f bootstrap/cache/*.php 2>/dev/null || true
"$PHP" artisan optimize:clear 2>&1 | tail -n 5

echo "Done. Guru: Jadwal Saya → Edit. Admin: Monitor Perubahan Jadwal."
