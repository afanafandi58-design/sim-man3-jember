#!/usr/bin/env bash
# Pulihkan HTTP 500 setelah ganti PHP / cache rusak (cPanel Terminal)
set -e
APP="${APP_ROOT:-$HOME/jurnal-kbm.man3jember.sch.id}"
[ -f "$APP/artisan" ] || APP="$HOME/public_html/jurnal-kbm.man3jember.sch.id"
[ -f "$APP/artisan" ] || { echo "ERROR: artisan tidak ditemukan"; exit 1; }
cd "$APP"
echo "ok app_root=$APP"

PHP=""
for bin in ea-php83 php83 /usr/local/bin/ea-php83 php; do
  command -v "$bin" >/dev/null 2>&1 || continue
  ver=$("$bin" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || true)
  case "$ver" in 8.3|8.4|8.5|9.*) PHP="$bin"; break ;; esac
done
[ -n "$PHP" ] || { echo "ERROR: butuh ea-php83"; exit 1; }
echo "ok php=$($PHP -v 2>/dev/null | head -1)"

echo "=== cek file inti (size) ==="
for f in public/index.php bootstrap/app.php routes/web.php config/app.php config/adminlte.php app/Providers/AppServiceProvider.php; do
  if [ -f "$f" ]; then
    echo "ok $f size=$(wc -c < "$f")"
  else
    echo "MISSING $f"
  fi
  if [ -f "$f" ] && [ ! -s "$f" ]; then
    echo "FATAL: $f is 0 bytes — restore dari backup/Git"
    exit 1
  fi
done

echo "=== php -l ==="
for f in bootstrap/app.php routes/web.php config/adminlte.php app/Providers/AppServiceProvider.php; do
  [ -f "$f" ] && "$PHP" -l "$f" 2>&1 || true
done

rm -f bootstrap/cache/*.php 2>/dev/null || true
rm -f storage/framework/cache/data/* 2>/dev/null || true
find storage/framework/views -name '*.php' -delete 2>/dev/null || true

"$PHP" artisan optimize:clear 2>&1 || true
"$PHP" artisan config:clear 2>&1 || true
"$PHP" artisan route:clear 2>&1 || true
"$PHP" artisan view:clear 2>&1 || true
echo "ok cache_cleared (tanpa config:cache — aman untuk pulih 500)"

echo "=== artisan about ==="
"$PHP" artisan about 2>&1 | head -20

echo "=== log tail ==="
tail -n 30 storage/logs/laravel.log 2>/dev/null || echo "(no log)"

echo "=== curl ==="
curl -sI "https://jurnal-kbm.man3jember.sch.id/login" | head -1
curl -sI "https://jurnal-kbm.man3jember.sch.id/lms" | head -1
echo "Done. Jika login masih 500, salin log tail ke chat Cursor."
