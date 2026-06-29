# ERD — Monitoring Infrastruktur Tiang Telekomunikasi

> Diagram ini mencakup seluruh 16 tabel sistem. Dibuat menggunakan Mermaid erDiagram.

```mermaid
erDiagram
    districts {
        bigint id PK
        varchar name UK
        timestamp created_at
        timestamp updated_at
    }

    areas {
        bigint id PK
        bigint district_id FK
        varchar name
        timestamp created_at
        timestamp updated_at
    }

    stos {
        bigint id PK
        bigint area_id FK
        varchar kode UK
        varchar nama
        timestamp deleted_at
        timestamp created_at
        timestamp updated_at
    }

    jenis_tiang {
        bigint id PK
        varchar nama UK
        text keterangan
        timestamp created_at
        timestamp updated_at
    }

    kondisi_tiang {
        bigint id PK
        varchar nama UK
        enum level
        timestamp created_at
        timestamp updated_at
    }

    users {
        bigint id PK
        varchar name
        varchar email UK
        varchar password
        enum role
        timestamp created_at
        timestamp updated_at
    }

    tiang_telekomunikasi {
        bigint id PK
        varchar kode_tiang UK
        varchar id_tiang_instansi
        bigint sto_id FK
        bigint jenis_tiang_id FK
        bigint kondisi_tiang_id FK
        decimal latitude
        decimal longitude
        text nama_jalan
        integer jml_tiang_operator_sekitar
        integer jml_kabel_dc_telkom
        integer jml_ku_telkom
        varchar nama_teknisi
        date tgl_input
        date tanggal_temuan
        enum status_verifikasi
        boolean has_anomali
        bigint created_by FK
        bigint updated_by FK
        timestamp deleted_at
        timestamp created_at
        timestamp updated_at
    }

    operator_isp {
        bigint id PK
        varchar nama_operator UK
        boolean is_predefined
        timestamp deleted_at
        timestamp created_at
        timestamp updated_at
    }

    tiang_operator {
        bigint id PK
        bigint tiang_id FK
        bigint operator_id FK
        integer jml_kabel_dc
        integer jml_ku
        integer jml_odp
        text keterangan_operator
        enum status_legalitas
        timestamp created_at
        timestamp updated_at
    }

    foto_tiang {
        bigint id PK
        bigint tiang_id FK
        enum jenis_foto
        varchar path_file
        varchar original_filename
        varchar mime_type
        bigint uploaded_by FK
        timestamp created_at
        timestamp updated_at
    }

    inspections {
        bigint id PK
        bigint tiang_id FK
        bigint inspected_by FK
        bigint kondisi_tiang_id FK
        decimal latitude
        decimal longitude
        text catatan
        timestamp inspected_at
        timestamp created_at
        timestamp updated_at
    }

    foto_inspeksi {
        bigint id PK
        bigint inspection_id FK
        varchar jenis_foto
        varchar path_file
        varchar original_filename
        varchar mime_type
        bigint uploaded_by FK
        timestamp created_at
        timestamp updated_at
    }

    anomali_log {
        bigint id PK
        bigint tiang_id FK
        enum jenis_anomali
        text keterangan
        enum status
        timestamp detected_at
        timestamp resolved_at
        bigint resolved_by FK
        timestamp created_at
        timestamp updated_at
    }

    import_histories {
        bigint id PK
        varchar filename
        bigint uploaded_by FK
        integer total_rows
        integer success_rows
        integer failed_rows
        integer progress_percent
        enum status
        timestamp started_at
        timestamp finished_at
        timestamp created_at
        timestamp updated_at
    }

    import_history_errors {
        bigint id PK
        bigint import_history_id FK
        integer row_number
        varchar column_name
        text error_message
        json raw_data
        timestamp created_at
        timestamp updated_at
    }

    activity_logs {
        bigint id PK
        bigint user_id FK
        varchar model_type
        bigint model_id
        varchar action
        varchar description
        json old_values
        json new_values
        varchar ip_address
        varchar user_agent
        timestamp created_at
    }

    %% ===== RELASI =====

    districts ||--o{ areas : "has many (restrict delete)"
    areas ||--o{ stos : "has many (restrict delete)"

    stos ||--o{ tiang_telekomunikasi : "restrict delete"
    jenis_tiang ||--o{ tiang_telekomunikasi : "restrict delete"
    kondisi_tiang ||--o{ tiang_telekomunikasi : "restrict delete"
    kondisi_tiang ||--o{ inspections : "restrict delete"

    users ||--o{ tiang_telekomunikasi : "created_by (null delete)"
    users ||--o{ tiang_telekomunikasi : "updated_by (null delete)"
    users ||--o{ foto_tiang : "uploaded_by (null delete)"
    users ||--o{ inspections : "inspected_by (null delete)"
    users ||--o{ foto_inspeksi : "uploaded_by (null delete)"
    users ||--o{ anomali_log : "resolved_by (null delete)"
    users ||--o{ import_histories : "uploaded_by (null delete)"
    users ||--o{ activity_logs : "user_id (null delete)"

    tiang_telekomunikasi ||--o{ tiang_operator : "cascade delete"
    tiang_telekomunikasi ||--o{ foto_tiang : "cascade delete"
    tiang_telekomunikasi ||--o{ inspections : "cascade delete"
    tiang_telekomunikasi ||--o{ anomali_log : "cascade delete"

    operator_isp ||--o{ tiang_operator : "restrict delete"

    inspections ||--o{ foto_inspeksi : "cascade delete"

    import_histories ||--o{ import_history_errors : "cascade delete"
```

## Keterangan Kolom Penting

| Tabel | Kolom | Keterangan |
|-------|-------|-----------|
| `tiang_telekomunikasi` | `kode_tiang` | Auto-generate: `TI-{STO_KODE}-{NNNNN}` via DB transaction + lockForUpdate |
| `tiang_telekomunikasi` | `id_tiang_instansi` | Partial unique index (NULL diabaikan di PostgreSQL) |
| `tiang_telekomunikasi` | `has_anomali` | **READONLY** — hanya `AnomalyDetectionService` yang boleh ubah |
| `tiang_telekomunikasi` | `status_verifikasi` | State machine: pending↔ok, pending↔ditolak, pending→double_input |
| `anomali_log` | `jenis_anomali` | 6 nilai: double_input, isp_tidak_teridentifikasi, kondisi_nok, verifikasi_pending, koordinat_tidak_valid, data_tidak_lengkap |
| `anomali_log` | *(partial unique)* | `UNIQUE (tiang_id, jenis_anomali) WHERE status = 'aktif'` |
| `activity_logs` | `model_type` | Alias tetap (bukan FQCN): tiang, inspection, foto_tiang, dll |

## Catatan PostGIS (belum dieksekusi)

```sql
-- Aktifkan PostGIS
CREATE EXTENSION IF NOT EXISTS postgis;

-- Tambah kolom geometry
ALTER TABLE tiang_telekomunikasi ADD COLUMN geom geometry(Point, 4326);

-- Isi dari kolom decimal yang ada
UPDATE tiang_telekomunikasi
SET geom = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326);

-- Index spatial
CREATE INDEX idx_tiang_geom ON tiang_telekomunikasi USING GIST(geom);

-- Kolom decimal TETAP dipertahankan paralel — aplikasi tidak perlu diubah
```
