#!/usr/bin/env bash
# Point 9F — area konten LMS blank (sidebar OK): restore view LMS + migrate + tes render
set -e
APP="${APP_ROOT:-$HOME/jurnal-kbm.man3jember.sch.id}"
cd "$APP"
[ -f artisan ] || { echo "ERROR: artisan missing in $APP"; exit 1; }

BASE="https://raw.githubusercontent.com/afanafandi58-design/sim-man3-jember/cursor/lms-modul-cd7e/jurnal-kbm"
REF="cursor/lms-modul-cd7e"
ARCHIVE="https://github.com/afanafandi58-design/sim-man3-jember/archive/refs/heads/${REF}.tar.gz"

PHP=""
for bin in ea-php83 php83 /usr/local/bin/ea-php83 /opt/cpanel/ea-php83/root/usr/bin/php php; do
  command -v "$bin" >/dev/null 2>&1 || continue
  ver=$("$bin" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || true)
  case "$ver" in 8.3|8.4|8.5|9.*) PHP="$bin"; break ;; esac
done
[ -n "$PHP" ] || { echo "ERROR: PHP 8.3+ CLI tidak ditemukan"; exit 1; }
echo "ok php=$($PHP -v 2>/dev/null | head -1)"

echo "=== backup views/lms ==="
BK="resources/views/lms.bak.$(date +%Y%m%d%H%M%S)"
cp -a resources/views/lms "$BK" 2>/dev/null || true
echo "ok backup=$BK"

echo "=== restore resources/views/lms from GitHub ==="
TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT
curl -fsSL "$ARCHIVE" | tar xz -C "$TMP"
SRC=$(find "$TMP" -type d -path '*/jurnal-kbm/resources/views/lms' | head -1)
[ -n "$SRC" ] || { echo "FATAL: lms views not in archive"; exit 1; }
rm -rf resources/views/lms
cp -a "$SRC" resources/views/lms
rm -f resources/views/lms/page.blade.php 2>/dev/null || true
echo "ok view_count=$(find resources/views/lms -name '*.blade.php' | wc -l)"
echo "ok dashboard_size=$(wc -c < resources/views/lms/dashboard.blade.php)"
head -1 resources/views/lms/dashboard.blade.php

echo "=== restore dashboard controller (if broken) ==="
curl -fsSL "$BASE/app/Http/Controllers/Lms/LmsDashboardController.php" -o app/Http/Controllers/Lms/LmsDashboardController.php
"$PHP" -l app/Http/Controllers/Lms/LmsDashboardController.php

rm -f bootstrap/cache/*.php 2>/dev/null || true
find storage/framework/views -name '*.php' -delete 2>/dev/null || true
"$PHP" artisan optimize:clear 2>&1 | tail -n 5

echo "=== migrate (tabel LMS) ==="
"$PHP" artisan migrate --force --no-interaction 2>&1 | tail -n 12

echo "=== render test dashboard ==="
"$PHP" artisan tinker --execute="
try {
  \$u = App\\Models\\User::query()->first();
  \$html = view('lms.dashboard', [
    'user' => \$u,
    'stats' => ['materials' => 0, 'assignments' => 0, 'quizzes' => 0, 'submissions' => 0],
    'announcements' => collect(),
  ])->render();
  echo 'render_len=' . strlen(\$html) . PHP_EOL;
  echo (str_contains(\$html, 'Dashboard LMS') ? 'ok title_in_html' : 'WARN no title') . PHP_EOL;
} catch (Throwable \$e) {
  echo 'RENDER_ERROR: ' . \$e->getMessage() . PHP_EOL;
  exit(1);
}
" 2>&1 | tail -n 6

echo "=== log tail ==="
tail -n 25 storage/logs/laravel.log 2>/dev/null | grep -iE 'lms|View|ERROR|Exception' | tail -n 10 || echo "(no matches)"

echo "Done 9F. Browser: logout → login → /lms dan /lms/materi → Ctrl+Shift+R"
echo "Harus tampil judul Dashboard LMS / tabel Materi (boleh kosong datanya)."
