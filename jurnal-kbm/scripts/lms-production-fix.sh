#!/usr/bin/env bash
# Run on cPanel Terminal (one command):
#   curl -fsSL "https://raw.githubusercontent.com/afanafandi58-design/sim-man3-jember/cursor/lms-modul-cd7e/jurnal-kbm/scripts/lms-production-fix.sh" | bash
set -euo pipefail
cd "${APP_ROOT:-$HOME/jurnal-kbm.man3jember.sch.id}"
if [[ ! -f artisan ]]; then
  cd "$HOME/public_html/jurnal-kbm.man3jember.sch.id" || true
fi
test -f artisan || { echo "ERROR: Laravel root not found"; pwd; exit 1; }

git fetch origin cursor/lms-modul-cd7e 2>/dev/null || true
git checkout cursor/lms-modul-cd7e 2>/dev/null || git checkout -B cursor/lms-modul-cd7e origin/cursor/lms-modul-cd7e
git pull origin cursor/lms-modul-cd7e 2>/dev/null || true

find resources/views/lms -name '*.blade.php' -print0 2>/dev/null | xargs -0 -r sed -i "s/@extends('lms.page')/@extends('adminlte::page')/g" || true
rm -f resources/views/lms/page.blade.php

php -r '
$f = "config/adminlte.php";
if (!is_file($f)) { exit(0); }
$c = file_get_contents($f);
while (substr_count($c, "'\''NavbarSuccessContrast'\'' =>") > 1) {
  $c = preg_replace("/\s*'\''NavbarSuccessContrast'\''\s*=>\s*\[.*?\],\s*/s", "", $c, 1);
}
$c = preg_replace("/('\''NavbarSuccessContrast'\''\s*=>\s*\[\s*'\''active'\''\s*=>\s*)(?:true|false)/", "${1}false", $c, 1);
file_put_contents($f, $c);
echo "adminlte ok\n";
'

php artisan migrate --force --no-interaction
php artisan optimize:clear
php artisan config:cache
php artisan route:cache

echo "=== HTTP /lms ==="
curl -sI "https://jurnal-kbm.man3jember.sch.id/lms" | head -1
echo "Done. Hard-refresh /lms in browser after login."
