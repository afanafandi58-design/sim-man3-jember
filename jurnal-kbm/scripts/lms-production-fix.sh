#!/usr/bin/env bash
# cPanel Terminal (2 baris — jangan curl | bash):
#   curl -fsSL "https://raw.githubusercontent.com/afanafandi58-design/sim-man3-jember/cursor/lms-modul-cd7e/jurnal-kbm/scripts/lms-production-fix.sh" -o /tmp/lms-fix.sh
#   bash /tmp/lms-fix.sh
set -e

APP=""
for d in \
  "$HOME/jurnal-kbm.man3jember.sch.id" \
  "$HOME/public_html/jurnal-kbm.man3jember.sch.id" \
  "$HOME/public_html/jurnal-kbm" \
  "$HOME/domains/jurnal-kbm.man3jember.sch.id/public_html"
do
  if [ -f "$d/artisan" ]; then
    APP="$d"
    break
  fi
done

if [ -z "$APP" ]; then
  ARTISAN=$(find "$HOME" -maxdepth 7 -name artisan -type f 2>/dev/null | head -1 || true)
  if [ -n "$ARTISAN" ]; then
    APP=$(dirname "$ARTISAN")
  fi
fi

if [ -z "$APP" ] || [ ! -f "$APP/artisan" ]; then
  echo "ERROR: folder Laravel (artisan) tidak ditemukan."
  find "$HOME" -maxdepth 5 -name artisan -type f 2>/dev/null | head -5 || true
  exit 1
fi

cd "$APP"
echo "ok app_root=$APP"

for stub in public/lms-deep-fix-once.php public/lms-repair-all-once.php public/*-once.php; do
  if [ -f "$stub" ] && [ ! -s "$stub" ]; then
    rm -f "$stub"
    echo "removed empty ${stub#public/}"
  fi
done

if [ -d .git ]; then
  git fetch origin cursor/lms-modul-cd7e 2>/dev/null || true
  git checkout cursor/lms-modul-cd7e 2>/dev/null || git checkout -B cursor/lms-modul-cd7e origin/cursor/lms-modul-cd7e 2>/dev/null || true
  git pull origin cursor/lms-modul-cd7e 2>/dev/null || true
  echo "ok git_pull_attempted"
else
  echo "WARN: bukan git repository — lewati git pull (view/migrate tetap jalan)"
fi

if [ -d resources/views/lms ]; then
  find resources/views/lms -name '*.blade.php' -type f 2>/dev/null | while read -r f; do
    sed -i "s/@extends('lms.page')/@extends('adminlte::page')/g" "$f" 2>/dev/null || true
    sed -i 's/@extends("lms.page")/@extends('\''adminlte::page'\'')/g' "$f" 2>/dev/null || true
  done
fi
rm -f resources/views/lms/page.blade.php 2>/dev/null || true
echo "ok views_patched"

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

php artisan migrate --force --no-interaction 2>&1 | tail -n 20
php artisan optimize:clear
php artisan config:cache
php artisan route:cache

echo "=== routes (lms) ==="
php artisan route:list --path=lms 2>&1 | head -15

echo "=== render test (dashboard) ==="
php artisan tinker --execute="
try {
  \$html = view('lms.dashboard', [
    'user' => App\\Models\\User::query()->first(),
    'stats' => ['materials' => 0, 'assignments' => 0, 'quizzes' => 0, 'submissions' => 0],
    'announcements' => collect(),
  ])->render();
  echo 'render_len=' . strlen(\$html) . PHP_EOL;
} catch (Throwable \$e) {
  echo 'RENDER_ERROR: ' . \$e->getMessage() . PHP_EOL;
}
" 2>&1 | tail -8

echo "=== log tail (errors) ==="
tail -n 20 storage/logs/laravel.log 2>/dev/null | grep -iE 'error|exception|lms|view' || echo "(no matching lines)"

echo "=== HTTP /lms ==="
curl -sI "https://jurnal-kbm.man3jember.sch.id/lms" | head -1
echo "Done. Login browser → /lms → Ctrl+Shift+R."
