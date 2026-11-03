#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="${1:-/tmp/buku-induk-deploy.tgz}"
cd "$ROOT"
if [[ ! -d vendor ]]; then
  composer install --no-dev --optimize-autoloader
fi
tar --exclude='config/config.local.php' \
    --exclude='storage/db/*.sqlite' \
    --exclude='storage/uploads/*' \
    --exclude='storage/cache/*' \
    --exclude='.git' \
    -czf "$OUT" .
echo "Wrote $OUT"
