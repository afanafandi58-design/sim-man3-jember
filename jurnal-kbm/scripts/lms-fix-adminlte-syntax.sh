#!/usr/bin/env bash
# Point 8B — pulihkan config/adminlte.php setelah syntax error baris ~656 (rollback regex rusak)
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

echo "ok restore config/adminlte.php from GitHub"
curl -fsSL "$BASE/config/adminlte.php" -o config/adminlte.php
echo "ok size=$(wc -c < config/adminlte.php)"

FIXPHP="/tmp/lms-fix-adminlte-$$.php"
cat > "$FIXPHP" <<'PHPEND'
<?php
$f = 'config/adminlte.php';
$c = file_get_contents($f);
if (str_contains($c, 'NavbarSuccessContrast')) {
    $c = preg_replace(
        "/('NavbarSuccessContrast'\\s*=>\\s*\\[\\s*\\n\\s*'active'\\s*=>\\s*)(true|false)/",
        '$1false',
        $c,
        1
    );
    file_put_contents($f, $c);
    echo "ok NavbarSuccessContrast_deactivated\n";
} else {
    echo "ok NavbarSuccessContrast_not_present\n";
}
PHPEND
"$PHP" "$FIXPHP"
rm -f "$FIXPHP"

echo "=== php -l ==="
"$PHP" -l config/adminlte.php

rm -f bootstrap/cache/*.php 2>/dev/null || true
"$PHP" artisan optimize:clear 2>&1 || true

echo "=== curl ==="
curl -sI "https://jurnal-kbm.man3jember.sch.id/login" | head -1
curl -sI "https://jurnal-kbm.man3jember.sch.id/lms" | head -1
echo "Done Point 8B. Tes login → /lms di browser."
