#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
BACKUP_DIR="${BACKUP_DIR:-$APP_DIR/storage/backups}"
KEEP_DAYS="${KEEP_DAYS:-14}"
STAMP="$(date +%Y-%m-%d_%H%M)"

mkdir -p "$BACKUP_DIR"

"$APP_DIR/scripts/db-backup.sh"

TAR_TARGETS=()
[ -d "$APP_DIR/storage/app/public" ] && TAR_TARGETS+=("storage/app/public")
[ -f "$APP_DIR/.env" ] && TAR_TARGETS+=(".env")

if [ "${#TAR_TARGETS[@]}" -gt 0 ]; then
  tar -czf "$BACKUP_DIR/files-$STAMP.tar.gz" -C "$APP_DIR" "${TAR_TARGETS[@]}"
fi

find "$BACKUP_DIR" -name 'files-*.tar.gz' -mtime "+$KEEP_DAYS" -delete

echo "Tam backup hazır: $BACKUP_DIR (db-$STAMP.* və files-$STAMP.tar.gz)"
