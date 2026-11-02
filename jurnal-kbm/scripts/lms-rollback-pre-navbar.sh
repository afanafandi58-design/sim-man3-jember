#!/usr/bin/env bash
# Rollback perubahan navbar kontras (pre-LMJ-9) + stabilkan LMS — cPanel Terminal
#   curl -fsSL ".../lms-rollback-pre-navbar.sh" -o /tmp/lms-rollback.sh && bash /tmp/lms-rollback.sh
set -e

APP=""
for d in \
  "$HOME/jurnal-kbm.man3jember.sch.id" \
  "$HOME/public_html/jurnal-kbm.man3jember.sch.id"
do
  [ -f "$d/artisan" ] && APP="$d" && break
done
[ -n "$APP" ] || APP=$(dirname "$(find "$HOME" -maxdepth 7 -name artisan -type f 2>/dev/null | head -1)" 2>/dev/null || true)
[ -f "$APP/artisan" ] || { echo "ERROR: artisan tidak ditemukan"; exit 1; }
cd "$APP"
echo "ok app_root=$APP"

PHP=""
for bin in ea-php83 php83 /usr/local/bin/ea-php83 php; do
  command -v "$bin" >/dev/null 2>&1 || continue
  ver=$("$bin" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || true)
  case "$ver" in 8.3|8.4|8.5|9.*) PHP="$bin"; break ;; esac
done
[ -n "$PHP" ] || { echo "ERROR: PHP 8.3+ (ea-php83) wajib — set di MultiPHP untuk jurnal-kbm"; exit 1; }
echo "ok php=$($PHP -v 2>/dev/null | head -1)"

echo "=== cek file inti ==="
for f in public/index.php bootstrap/app.php routes/web.php app/Providers/AppServiceProvider.php config/adminlte.php; do
  if [ ! -f "$f" ] || [ ! -s "$f" ]; then
    echo "FATAL: $f hilang atau 0 byte"
    echo "Jalankan restore 4 file: https://jurnal-kbm.man3jember.sch.id/sim-restore-once.php?token=r3st0re_M4N3_20261031_k9Pm2"
    echo "(Upload sim-restore-once.php dari Project Context jika URL 404)"
    exit 1
  fi
  echo "ok $f size=$(wc -c < "$f")"
done

# Jangan edit adminlte jika sudah valid (edit pada file rusak = syntax error baris 656)
if "$PHP" -l config/adminlte.php >/dev/null 2>&1; then
  echo "ok adminlte_php_l_skip_edit"
else
  echo "WARN adminlte invalid — restore dulu: curl config/adminlte.php from GitHub (Point 8D)"
fi
rm -f public/css/navbar-success-contrast.css 2>/dev/null && echo "ok css_removed" || echo "note css_already_gone"

if [ -d resources/views/lms ]; then
  find resources/views/lms -name '*.blade.php' -type f 2>/dev/null | while read -r f; do
    sed -i "s/@extends('lms.page')/@extends('adminlte::page')/g" "$f" 2>/dev/null || true
    sed -i 's/@extends("lms.page")/@extends('\''adminlte::page'\'')/g' "$f" 2>/dev/null || true
  done
fi
rm -f resources/views/lms/page.blade.php 2>/dev/null || true
echo "ok lms_views=adminlte_page"

for stub in public/*-once.php; do
  [ -f "$stub" ] && [ ! -s "$stub" ] && rm -f "$stub" && echo "removed empty $(basename "$stub")"
done

rm -f bootstrap/cache/*.php 2>/dev/null || true
"$PHP" artisan optimize:clear 2>&1 || true
"$PHP" artisan config:clear 2>&1 || true
"$PHP" artisan route:clear 2>&1 || true
"$PHP" artisan view:clear 2>&1 || true
echo "ok cache_cleared"

echo "=== php -l ==="
"$PHP" -l config/adminlte.php 2>&1
"$PHP" -l routes/web.php 2>&1

echo "=== migrate (LMS tables) ==="
"$PHP" artisan migrate --force --no-interaction 2>&1 | tail -n 15

echo "=== curl ==="
curl -sI "https://jurnal-kbm.man3jember.sch.id/login" | head -1
curl -sI "https://jurnal-kbm.man3jember.sch.id/lms" | head -1

echo "=== log tail ==="
tail -n 15 storage/logs/laravel.log 2>/dev/null | grep -iE 'error|exception' || echo "(no recent errors in tail)"

echo "Done. Rollback UI navbar selesai; tes login → /lms"
