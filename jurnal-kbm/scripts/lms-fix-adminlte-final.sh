#!/usr/bin/env bash
# Point 9A-fix — adminlte.php valid + cache (blank /lms)
set -e
APP="${APP_ROOT:-$HOME/jurnal-kbm.man3jember.sch.id}"
cd "$APP"
[ -f artisan ] || { echo "ERROR: artisan missing"; exit 1; }
BASE="https://raw.githubusercontent.com/afanafandi58-design/sim-man3-jember/cursor/lms-modul-cd7e/jurnal-kbm"

PHP=""
for bin in ea-php83 php83 /usr/local/bin/ea-php83 php; do
  command -v "$bin" >/dev/null 2>&1 || continue
  ver=$("$bin" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || true)
  case "$ver" in 8.3|8.4|8.5|9.*) PHP="$bin"; break ;; esac
done
[ -n "$PHP" ] || PHP=php
echo "ok php=$($PHP -v 2>/dev/null | head -1)"

echo "=== backup adminlte rusak ==="
cp -a config/adminlte.php "config/adminlte.php.bak.$(date +%Y%m%d%H%M%S)" 2>/dev/null || true

echo "=== restore config (GitHub) ==="
curl -fsSL "$BASE/config/adminlte.php" -o config/adminlte.php
curl -fsSL "$BASE/config/logging.php" -o config/logging.php
echo "ok adminlte size=$(wc -c < config/adminlte.php)"

echo "=== php -l ==="
"$PHP" -l config/adminlte.php
"$PHP" -l config/logging.php

rm -f bootstrap/cache/*.php 2>/dev/null || true
find storage/framework/views -name '*.php' -delete 2>/dev/null || true
"$PHP" artisan optimize:clear 2>&1 | tail -n 8

echo "=== artisan about ==="
"$PHP" artisan about 2>&1 | head -8

echo "=== curl ==="
curl -sI "https://jurnal-kbm.man3jember.sch.id/login" | head -1

echo "Done 9A-fix. Login browser → /lms (Ctrl+Shift+R). JANGAN jalankan rollback lama / repair-all."
