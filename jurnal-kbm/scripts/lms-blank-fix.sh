#!/usr/bin/env bash
# Point 9 — /lms blank putih setelah login (view + migrate + cek route)
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
echo "ok php=$($PHP -v 2>/dev/null | head -1)"

BASE="https://raw.githubusercontent.com/afanafandi58-design/sim-man3-jember/cursor/lms-modul-cd7e/jurnal-kbm"

if ! grep -q "lms.php" routes/web.php 2>/dev/null; then
  echo "WARN routes/web.php tanpa require lms.php — restore routes"
  curl -fsSL "$BASE/routes/web.php" -o routes/web.php
fi
echo "ok routes/web.php size=$(wc -c < routes/web.php)"

if [ -f resources/views/lms/dashboard.blade.php ]; then
  sed -i "s/@extends('lms.page')/@extends('adminlte::page')/g" resources/views/lms/*.blade.php 2>/dev/null || true
  sed -i "s/@extends('lms.page')/@extends('adminlte::page')/g" resources/views/lms/*/*.blade.php 2>/dev/null || true
  rm -f resources/views/lms/page.blade.php 2>/dev/null || true
  echo "ok lms_views_patched"
else
  echo "WARN resources/views/lms/dashboard.blade.php MISSING — deploy modul LMS belum lengkap"
fi

rm -f bootstrap/cache/*.php 2>/dev/null || true
find storage/framework/views -name '*.php' -delete 2>/dev/null || true
"$PHP" artisan view:clear 2>&1 || true
"$PHP" artisan optimize:clear 2>&1 || true

echo "=== migrate ==="
"$PHP" artisan migrate --force --no-interaction 2>&1 | tail -n 15

echo "=== route lms (sample) ==="
"$PHP" artisan route:list --path=lms 2>&1 | head -n 12

echo "=== log tail lms/view ==="
tail -n 40 storage/logs/laravel.log 2>/dev/null | grep -iE 'lms|View|ERROR|Exception' | tail -n 15 || echo "(no matches)"

echo "Done Point 9 script. Browser: login → /lms Ctrl+Shift+R"
