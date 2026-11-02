#!/usr/bin/env bash
# Point 8E — perbaiki view LMS saja; TIDAK mengubah config/adminlte.php
set -e
APP="${APP_ROOT:-$HOME/jurnal-kbm.man3jember.sch.id}"
[ -f "$APP/artisan" ] || { echo "ERROR: artisan tidak ditemukan"; exit 1; }
cd "$APP"

PHP=""
for bin in ea-php83 php83 /usr/local/bin/ea-php83 php; do
  command -v "$bin" >/dev/null 2>&1 || continue
  ver=$("$bin" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || true)
  case "$ver" in 8.3|8.4|8.5|9.*) PHP="$bin"; break ;; esac
done
[ -n "$PHP" ] || { echo "ERROR: PHP 8.3+ wajib"; exit 1; }
echo "ok php=$($PHP -v 2>/dev/null | head -1)"

if [ -d resources/views/lms ]; then
  find resources/views/lms -name '*.blade.php' -type f 2>/dev/null | while read -r f; do
    sed -i "s/@extends('lms.page')/@extends('adminlte::page')/g" "$f" 2>/dev/null || true
    sed -i 's/@extends("lms.page")/@extends('\''adminlte::page'\'')/g' "$f" 2>/dev/null || true
  done
fi
rm -f resources/views/lms/page.blade.php 2>/dev/null || true
echo "ok lms_views=adminlte_page"

rm -f bootstrap/cache/*.php 2>/dev/null || true
"$PHP" artisan optimize:clear 2>&1 || true
"$PHP" artisan view:clear 2>&1 || true

echo "=== migrate ==="
"$PHP" artisan migrate --force --no-interaction 2>&1 | tail -n 12

echo "=== curl ==="
curl -sI "https://jurnal-kbm.man3jember.sch.id/login" | head -1
curl -sI "https://jurnal-kbm.man3jember.sch.id/lms" | head -1
echo "Done 8E. Tes login → /lms di browser."
