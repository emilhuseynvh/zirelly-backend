#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
BACKUP_DIR="${BACKUP_DIR:-$APP_DIR/storage/backups}"
KEEP_DAYS="${KEEP_DAYS:-14}"
STAMP="$(date +%Y-%m-%d_%H%M)"

mkdir -p "$BACKUP_DIR"

env_val() {
  grep -E "^$1=" "$APP_DIR/.env" | head -1 | cut -d= -f2- | tr -d '"'
}

CONNECTION="$(env_val DB_CONNECTION)"

if [ "$CONNECTION" = "sqlite" ]; then
  DB_FILE="$(env_val DB_DATABASE)"
  DB_FILE="${DB_FILE:-$APP_DIR/database/database.sqlite}"
  sqlite3 "$DB_FILE" ".backup '$BACKUP_DIR/db-$STAMP.sqlite'"
  gzip "$BACKUP_DIR/db-$STAMP.sqlite"
else
  MYSQL_PWD="$(env_val DB_PASSWORD)" mysqldump \
    --host="$(env_val DB_HOST)" \
    --port="$(env_val DB_PORT)" \
    --user="$(env_val DB_USERNAME)" \
    --single-transaction --quick --routines \
    "$(env_val DB_DATABASE)" | gzip > "$BACKUP_DIR/db-$STAMP.sql.gz"
fi

find "$BACKUP_DIR" -name 'db-*.gz' -mtime "+$KEEP_DAYS" -delete

echo "Backup hazır: $BACKUP_DIR/db-$STAMP.*.gz"
