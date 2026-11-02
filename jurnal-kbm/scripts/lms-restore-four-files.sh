#!/usr/bin/env bash
# Restore 4 core PHP files from GitHub raw (cPanel Terminal) — alternatif Point 6 jika one-shot 500
set -e
APP="${APP_ROOT:-$HOME/jurnal-kbm.man3jember.sch.id}"
[ -f "$APP/artisan" ] || { echo "ERROR: artisan tidak ditemukan di $APP"; exit 1; }
cd "$APP"
BASE="https://raw.githubusercontent.com/afanafandi58-design/sim-man3-jember/cursor/lms-modul-cd7e/jurnal-kbm"
echo "ok app_root=$APP"
for rel in routes/web.php bootstrap/app.php app/Providers/AppServiceProvider.php config/adminlte.php; do
  curl -fsSL "$BASE/$rel" -o "$rel"
  echo "ok $rel size=$(wc -c < "$rel")"
done
PHP=""
for bin in ea-php83 php83 /usr/local/bin/ea-php83 php; do
  command -v "$bin" >/dev/null 2>&1 || continue
  ver=$("$bin" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || true)
  case "$ver" in 8.3|8.4|8.5|9.*) PHP="$bin"; break ;; esac
done
[ -n "$PHP" ] || { echo "ERROR: ea-php83 wajib"; exit 1; }
rm -f bootstrap/cache/*.php 2>/dev/null || true
"$PHP" artisan optimize:clear 2>&1 || true
echo "Done restore 4 file. Lanjut: bash /tmp/lms-500.sh lalu lms-rollback.sh"
