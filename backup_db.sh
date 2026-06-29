#!/bin/bash
# backup_db.sh — Backup database PostgreSQL monitoring tiang
# Usage: ./backup_db.sh [DB_USER] [DB_HOST] [DB_NAME]
#
# Contoh: ./backup_db.sh postgres 127.0.0.1 monitoring_tiang

DB_USER=${1:-postgres}
DB_HOST=${2:-127.0.0.1}
DB_NAME=${3:-monitoring_tiang}
BACKUP_DIR="./backups"
FILENAME="backup_${DB_NAME}_$(date +%Y%m%d_%H%M).dump"

mkdir -p "$BACKUP_DIR"

echo "📦 Memulai backup database ${DB_NAME}..."
pg_dump -U "$DB_USER" -h "$DB_HOST" -d "$DB_NAME" -F c -f "${BACKUP_DIR}/${FILENAME}"

if [ $? -eq 0 ]; then
    echo "✅ Backup berhasil: ${BACKUP_DIR}/${FILENAME}"
    echo "   Ukuran: $(du -sh "${BACKUP_DIR}/${FILENAME}" | cut -f1)"
else
    echo "❌ Backup gagal!"
    exit 1
fi
