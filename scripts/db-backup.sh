#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
BACKUP_DIR="${BACKUP_DIR:-$APP_DIR/storage/backups}"
KEEP_DAYS="${KEEP_DAYS:-14}"
STAMP="$(date +%Y-%m-%d_%H%M)"

mkdir -p "$BACKUP_DIR"

# .env sətirlərinin əvvəlindəki boşluqlara dözümlüdür (məs. " DB_HOST=...")
env_val() {
  grep -E "^[[:space:]]*$1=" "$APP_DIR/.env" | head -1 | sed -E "s/^[[:space:]]*$1=//" | tr -d '"'
}

CONNECTION="$(env_val DB_CONNECTION)"

if [ "$CONNECTION" = "sqlite" ]; then
  DB_FILE="$(env_val DB_DATABASE)"
  DB_FILE="${DB_FILE:-$APP_DIR/database/database.sqlite}"
  sqlite3 "$DB_FILE" ".backup '$BACKUP_DIR/db-$STAMP.sqlite'"
  gzip "$BACKUP_DIR/db-$STAMP.sqlite"
else
  DB_HOST="$(env_val DB_HOST)"
  DB_PORT="$(env_val DB_PORT)"
  MYSQL_PWD="$(env_val DB_PASSWORD)" mysqldump \
    --host="${DB_HOST:-127.0.0.1}" \
    --port="${DB_PORT:-3306}" \
    --user="$(env_val DB_USERNAME)" \
    --single-transaction --quick --routines \
    "$(env_val DB_DATABASE)" | gzip > "$BACKUP_DIR/db-$STAMP.sql.gz"
fi

find "$BACKUP_DIR" -name 'db-*.gz' -mtime "+$KEEP_DAYS" -delete

echo "Backup hazır: $BACKUP_DIR/db-$STAMP.*.gz"
