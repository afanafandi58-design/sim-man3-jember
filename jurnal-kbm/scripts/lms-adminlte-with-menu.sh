#!/usr/bin/env bash
# Point 9E — restore adminlte.php LENGKAP (menu LMS PEMBELAJARAN).
# Jangan pakai lms-use-safe-adminlte.sh untuk kondisi final (versi safe = tanpa menu LMS).
set -e
APP="${APP_ROOT:-$HOME/jurnal-kbm.man3jember.sch.id}"
cd "$APP"
[ -f artisan ] || { echo "ERROR: artisan missing in $APP"; exit 1; }
BASE="https://raw.githubusercontent.com/afanafandi58-design/sim-man3-jember/cursor/lms-modul-cd7e/jurnal-kbm"

PHP=""
for bin in ea-php83 php83 /usr/local/bin/ea-php83 php; do
  command -v "$bin" >/dev/null 2>&1 || continue
  ver=$("$bin" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || true)
  case "$ver" in 8.3|8.4|8.5|9.*) PHP="$bin"; break ;; esac
done
[ -n "$PHP" ] || PHP=php
echo "ok php=$($PHP -v 2>/dev/null | head -1)"

cp -a config/adminlte.php "config/adminlte.php.bak.$(date +%Y%m%d%H%M%S)" 2>/dev/null || true

echo "=== restore full config/adminlte.php (with LMS menu) ==="
curl -fsSL "$BASE/config/adminlte.php" -o config/adminlte.php
SIZE=$(wc -c < config/adminlte.php)
echo "ok adminlte size=$SIZE (expect ~24800+, NOT 22618 safe-only)"

if ! grep -q 'LMS PEMBELAJARAN' config/adminlte.php; then
  echo "FATAL: restored adminlte.php has no LMS PEMBELAJARAN header"
  exit 1
fi

echo "=== php -l & include ==="
"$PHP" -l config/adminlte.php
"$PHP" -r 'include "config/adminlte.php"; echo "ok include_array\n";'

if ! grep -q "role-lms" app/Providers/AppServiceProvider.php 2>/dev/null; then
  echo "=== restore AppServiceProvider (LMS gates) ==="
  curl -fsSL "$BASE/app/Providers/AppServiceProvider.php" -o app/Providers/AppServiceProvider.php
  "$PHP" -l app/Providers/AppServiceProvider.php
fi

rm -f bootstrap/cache/*.php 2>/dev/null || true
find storage/framework/views -name '*.php' -delete 2>/dev/null || true
"$PHP" artisan optimize:clear 2>&1 | tail -n 8

echo "=== menu check (grep) ==="
grep -c 'LMS PEMBELAJARAN' config/adminlte.php | xargs -I{} echo "ok lms_header_count={}"

echo "Done 9E. Logout → login (admin/guru/kepala) → sidebar harus ada LMS PEMBELAJARAN → /lms Ctrl+Shift+R"
echo "JANGAN jalankan lagi: lms-use-safe-adminlte.sh (menghapus menu LMS)."
