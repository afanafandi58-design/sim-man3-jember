#!/usr/bin/env bash
# Point 7B — perbaiki error LoadConfiguration (config 0 byte / rusak)
set -e
APP="${APP_ROOT:-$HOME/jurnal-kbm.man3jember.sch.id}"
[ -f "$APP/artisan" ] || { echo "ERROR: artisan tidak ditemukan"; exit 1; }
cd "$APP"
BASE="https://raw.githubusercontent.com/afanafandi58-design/sim-man3-jember/cursor/lms-modul-cd7e/jurnal-kbm"

PHP=""
for bin in ea-php83 php83 /usr/local/bin/ea-php83 php; do
  command -v "$bin" >/dev/null 2>&1 || continue
  ver=$("$bin" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || true)
  case "$ver" in 8.3|8.4|8.5|9.*) PHP="$bin"; break ;; esac
done
[ -n "$PHP" ] || { echo "ERROR: ea-php83 wajib"; exit 1; }
echo "ok app_root=$APP"
echo "ok php=$($PHP -v 2>/dev/null | head -1)"

echo "=== scan config/*.php (size) ==="
ZERO=0
for f in config/*.php; do
  [ -f "$f" ] || continue
  sz=$(wc -c < "$f")
  if [ "$sz" -eq 0 ]; then
    echo "ZERO $f"
    ZERO=1
  else
    echo "ok $f size=$sz"
  fi
done

CONFIGS=(
  config/app.php
  config/auth.php
  config/cache.php
  config/database.php
  config/filesystems.php
  config/logging.php
  config/mail.php
  config/queue.php
  config/services.php
  config/session.php
  config/madrasah.php
  config/operational_date.php
  config/adminlte.php
)

echo "=== restore config dari GitHub (overwrite aman) ==="
for rel in "${CONFIGS[@]}"; do
  curl -fsSL "$BASE/$rel" -o "$rel"
  echo "ok $rel size=$(wc -c < "$rel")"
done

echo "=== php -l config ==="
for rel in "${CONFIGS[@]}"; do
  "$PHP" -l "$rel" 2>&1 || true
done

rm -f bootstrap/cache/*.php 2>/dev/null || true
find storage/framework/views -name '*.php' -delete 2>/dev/null || true

echo "=== artisan optimize:clear ==="
"$PHP" artisan optimize:clear 2>&1 || true

echo "=== artisan about (harus tanpa stack trace) ==="
"$PHP" artisan about 2>&1 | head -15

echo "=== curl login ==="
curl -sI "https://jurnal-kbm.man3jember.sch.id/login" | head -1
echo "Done Point 7B. Jika login 200/302, lanjut Point 8 (lms-rollback-pre-navbar.sh)."
