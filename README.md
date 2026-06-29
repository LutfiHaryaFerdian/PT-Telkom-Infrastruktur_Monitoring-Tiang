# Monitoring Infrastruktur Tiang Telekomunikasi
## PT Telkom Infrastruktur Indonesia — District Lampung

Sistem Informasi Monitoring Infrastruktur Tiang Telekomunikasi Berbasis Web GIS.

---

## Stack Teknologi

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 11+ (PHP 8.2+) |
| Database | PostgreSQL 14+ |
| GIS Frontend | Leaflet.js + Leaflet.markercluster (CDN) |
| Frontend | Blade + Bootstrap 5 |
| Auth | Laravel built-in + Gate/Policy + Custom Middleware |
| Storage | Laravel Storage (public disk) |
| Table | AJAX server-side (DataTables-compatible) |
| Queue | Laravel Queue (database driver) |
| PDF | barryvdh/laravel-dompdf |
| Excel | maatwebsite/excel |
| Charts | Chart.js (CDN) |

---

## Requirement

- PHP 8.2+
- PostgreSQL 14+ (WAJIB — tidak kompatibel dengan MySQL/SQLite)
- Composer 2.x

---

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan queue:work --tries=3 --timeout=600
```

### Akun Default

| Email | Password | Role |
|-------|----------|------|
| admin@telkominf.com | password | admin |
| teknisi@telkominf.com | password | teknisi |

---

## Struktur Storage

```
storage/app/public/
  foto_tiang/{tiang_id}/{jenis}.{ext}
  foto_inspeksi/{inspection_id}/{uuid}.{ext}
  exports/{user_id}/{filename}_{timestamp}.{ext}
```

File export TTL 24 jam — otomatis dihapus via scheduler `cleanup:exports`.

---

## Artisan Commands

| Command | Deskripsi |
|---------|-----------|
| `php artisan tiang:purge {id}` | Hard delete permanen tiang + file fisik |
| `php artisan tiang:purge {id} --force` | Skip konfirmasi |
| `php artisan cleanup:exports` | Hapus file export > 24 jam |
| `php artisan cleanup:exports --dry-run` | Preview tanpa hapus |

---

## Backup & Restore

```bash
# Backup
./backup_db.sh postgres 127.0.0.1 monitoring_tiang

# Restore test (WAJIB sebelum produksi)
./restore_test.sh backups/backup_YYYYMMDD.dump postgres 127.0.0.1 monitoring_tiang
```

---

## Catatan Enum PostgreSQL

```php
// Tambah nilai enum baru via migration khusus
DB::statement("ALTER TYPE kondisi_level ADD VALUE 'kritis'");
// JANGAN ALTER TABLE langsung di production
```

## Catatan PostGIS (Upgrade Opsional — Belum Dieksekusi)

```sql
CREATE EXTENSION IF NOT EXISTS postgis;
ALTER TABLE tiang_telekomunikasi ADD COLUMN geom geometry(Point, 4326);
UPDATE tiang_telekomunikasi SET geom = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326);
CREATE INDEX idx_tiang_geom ON tiang_telekomunikasi USING GIST(geom);
-- Kolom decimal tetap dipertahankan paralel
```

---

## Fase 2 (Belum Diimplementasikan)

- Manajemen PKS per ISP per tiang
- Klasifikasi Cluster A / B / C
- Workflow SP1 → SP2 → SP3
- Role Supervisor/AOM dengan middleware, policy, dashboard khusus
- PostGIS untuk query spasial lanjutan

---

Lihat [`docs/ERD.md`](docs/ERD.md) dan [`docs/ALUR_SISTEM.md`](docs/ALUR_SISTEM.md) untuk dokumentasi lengkap.
