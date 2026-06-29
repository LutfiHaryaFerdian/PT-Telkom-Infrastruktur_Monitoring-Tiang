#!/bin/bash
# restore_test.sh — Test restore backup PostgreSQL ke database sementara
# Wajib dijalankan sebelum produksi untuk memvalidasi backup!
#
# Usage: ./restore_test.sh [BACKUP_FILE] [DB_USER] [DB_HOST] [DB_PROD]
# Contoh: ./restore_test.sh backups/backup_monitoring_tiang_20260101_0000.dump postgres 127.0.0.1 monitoring_tiang

BACKUP_FILE=${1}
DB_USER=${2:-postgres}
DB_HOST=${3:-127.0.0.1}
DB_PROD=${4:-monitoring_tiang}
DB_TEST="db_restore_test_$(date +%s)"

if [ -z "$BACKUP_FILE" ] || [ ! -f "$BACKUP_FILE" ]; then
    echo "❌ File backup tidak ditemukan: $BACKUP_FILE"
    echo "Usage: ./restore_test.sh <backup_file> [user] [host] [prod_db]"
    exit 1
fi

echo "🔍 Memulai restore test..."
echo "   File: $BACKUP_FILE"
echo "   Database test: $DB_TEST"

# 1. Buat database sementara
echo "\n[1/5] Membuat database test..."
psql -U "$DB_USER" -h "$DB_HOST" -d postgres -c "CREATE DATABASE $DB_TEST;" || exit 1

# 2. Restore backup
echo "[2/5] Restore backup..."
pg_restore -U "$DB_USER" -h "$DB_HOST" -d "$DB_TEST" "$BACKUP_FILE"
if [ $? -ne 0 ]; then
    echo "⚠️  Restore selesai dengan warning (biasanya aman, cek output di atas)"
fi

# 3. Cek jumlah tiang di database test
echo "[3/5] Cek jumlah data di database test..."
COUNT_TEST=$(psql -U "$DB_USER" -h "$DB_HOST" -d "$DB_TEST" -t -c "SELECT COUNT(*) FROM tiang_telekomunikasi;" 2>/dev/null | tr -d ' ')
echo "   Total tiang di DB test: $COUNT_TEST"

# 4. Cek jumlah tiang di database produksi
echo "[4/5] Cek jumlah data di database produksi..."
COUNT_PROD=$(psql -U "$DB_USER" -h "$DB_HOST" -d "$DB_PROD" -t -c "SELECT COUNT(*) FROM tiang_telekomunikasi;" 2>/dev/null | tr -d ' ')
echo "   Total tiang di DB produksi: $COUNT_PROD"

# 5. Bandingkan
echo "[5/5] Validasi..."
if [ "$COUNT_TEST" = "$COUNT_PROD" ]; then
    echo "✅ Restore berhasil! Jumlah data cocok ($COUNT_TEST tiang)."
else
    echo "⚠️  Jumlah data BERBEDA: Test=$COUNT_TEST vs Produksi=$COUNT_PROD"
    echo "   Periksa backup secara manual!"
fi

# Cleanup: hapus database test
echo "\n🗑  Menghapus database test..."
psql -U "$DB_USER" -h "$DB_HOST" -d postgres -c "DROP DATABASE IF EXISTS $DB_TEST;"
echo "✅ Database test dihapus."
