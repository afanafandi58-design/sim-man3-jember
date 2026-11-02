#!/usr/bin/env bash
# Deploy kartu "Tata cara" LMS (config + views) tanpa mengubah database
set -e
APP="${APP_ROOT:-$HOME/jurnal-kbm.man3jember.sch.id}"
cd "$APP"
[ -f artisan ] || { echo "ERROR: artisan missing"; exit 1; }
BASE="https://raw.githubusercontent.com/afanafandi58-design/sim-man3-jember/cursor/lms-modul-cd7e/jurnal-kbm"
REF="cursor/lms-modul-cd7e"
ARCHIVE="https://github.com/afanafandi58-design/sim-man3-jember/archive/refs/heads/${REF}.tar.gz"

PHP=""
for bin in ea-php83 php83 /usr/local/bin/ea-php83 php; do
  command -v "$bin" >/dev/null 2>&1 || continue
  ver=$("$bin" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || true)
  case "$ver" in 8.3|8.4|8.5|9.*) PHP="$bin"; break ;; esac
done
[ -n "$PHP" ] || PHP=php

echo "=== restore config/lms_guides.php ==="
curl -fsSL "$BASE/config/lms_guides.php" -o config/lms_guides.php
"$PHP" -l config/lms_guides.php

echo "=== restore views/lms (guides partial + includes) ==="
TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT
curl -fsSL "$ARCHIVE" | tar xz -C "$TMP"
SRC=$(find "$TMP" -type d -path '*/jurnal-kbm/resources/views/lms' | head -1)
rm -rf resources/views/lms
cp -a "$SRC" resources/views/lms
rm -f resources/views/lms/page.blade.php 2>/dev/null || true

rm -f bootstrap/cache/*.php 2>/dev/null || true
find storage/framework/views -name '*.php' -delete 2>/dev/null || true
"$PHP" artisan optimize:clear 2>&1 | tail -n 4

echo "ok lms_guides pages=$(grep -c \"'title'\" config/lms_guides.php || true)"
echo "Done. Browser: buka submenu LMS → kartu biru Tata cara (bisa dilipat). Ctrl+Shift+R"
