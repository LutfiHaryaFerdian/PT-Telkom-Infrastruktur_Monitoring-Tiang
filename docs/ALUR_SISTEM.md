# ALUR SISTEM — Monitoring Infrastruktur Tiang Telekomunikasi

## 1. Alur Input Data Tiang (Manual via Web)

```mermaid
flowchart TD
    A[Teknisi Login] --> B[Form Input Tiang]
    B --> C{Validasi TiangRequest}
    C -- Gagal --> D[Tampilkan Error Validasi]
    D --> B
    C -- OK --> E[DB Transaction]
    E --> F[Generate kode_tiang\nFOR UPDATE lock]
    F --> G[Simpan tiang_telekomunikasi\ncreated_by = user_id]
    G --> H[Simpan tiang_operator jika ada ISP]
    H --> I[Catat ActivityLog\n'created']
    I --> J[AnomalyDetectionService.detect]
    J --> K{6 Pengecekan Anomali}
    K --> L[Insert anomali_log\nJika ada anomali]
    L --> M[Update has_anomali via saveQuietly]
    M --> N[Redirect ke show tiang]
```

## 2. Alur Import Data Excel

```mermaid
flowchart TD
    A[Admin Upload File Excel] --> B[Simpan ImportHistory\nstatus=processing]
    B --> C[Dispatch ImportJob ke Queue]
    C --> D[Frontend polling /api/import/{id}/progress\nsetiap 3 detik]
    D --> E[Job berjalan di background]
    E --> F[Baca baris Excel per baris]
    F --> G{Validasi baris}
    G -- Error --> H[ImportHistoryError::create\nraw_data = JSON baris]
    G -- OK --> I[Proses & Simpan Tiang]
    I --> J[Update progress_percent\nsetiap 50 baris]
    J --> G
    I --> K[Setelah semua baris]
    K --> L[Update ImportHistory\nstatus=done/failed]
    L --> M[Dispatch RunAnomalyDetectionJob]
    M --> N[Detect anomali batch tiang baru]
```

## 3. Alur Verifikasi Tiang (State Machine)

```mermaid
stateDiagram-v2
    [*] --> pending : Tiang dibuat (default)

    pending --> ok : Admin approve
    pending --> ditolak : Admin tolak
    pending --> double_input : Deteksi anomali double input

    ditolak --> pending : Teknisi ajukan ulang (SATU-SATUNYA jalur mundur)

    ok --> [*] : Final
    double_input --> [*] : Perlu investigasi manual

    note right of ok
        Tidak bisa berubah ke status lain
    end note

    note right of double_input
        Tidak bisa langsung ke ok
        Harus investigasi manual
    end note
```

## 4. Alur Deteksi Anomali (AnomalyDetectionService)

```mermaid
flowchart TD
    A[detect tiang] --> B[DB::transaction]
    B --> C1[1. Cek Double Input\nABS lat-lat < 0.0001]
    B --> C2[2. Cek Koordinat Tidak Valid\nlat out -7 to -4]
    B --> C3[3. Cek Kondisi NOK\nlevel perlu_perhatian/rusak]
    B --> C4[4. Cek ISP Tidak Teridentifikasi\nnon-predefined + kosong keterangan]
    B --> C5[5. Cek Verifikasi Pending > 3 Hari]
    B --> C6[6. Cek Data Tidak Lengkap\nteknisi/jalan/foto kosong]

    C1 & C2 & C3 & C4 & C5 & C6 --> D{Insert anomali_log}
    D -- UniqueConstraintViolation --> E[Abaikan\nduplikat aktif]
    D -- OK --> F[Record tersimpan]

    E & F --> G[UPDATE has_anomali\nberdasarkan anomali aktif]
    G --> H[saveQuietly\ntanpa trigger event]
```

## 5. Alur Export Data

```mermaid
flowchart TD
    A[User Request Export] --> B{Format?}
    B -- PDF --> C{Jumlah baris}
    C -- "> 1000" --> D[Return 422\nGunakan Excel]
    C -- "<= 1000" --> E[Generate PDF\nDomPDF]
    B -- Excel --> F[Generate XLSX\nPhpSpreadsheet]
    B -- CSV --> G[Generate CSV\nUTF-8 BOM]

    E & F & G --> H[Simpan ke storage/exports/{user_id}/]
    H --> I[TTL 24 jam\nCleanup otomatis]
```

## 6. Prioritas Warna Marker Peta (Leaflet)

```mermaid
flowchart TD
    A[Tiang] --> B{has_anomali = true?}
    B -- Ya --> C[🔴 Merah\nPRIORITAS TERTINGGI]
    B -- Tidak --> D{status_verifikasi = pending?}
    D -- Ya --> E[🟡 Kuning]
    D -- Tidak --> F[🟢 Hijau\nok]
```
