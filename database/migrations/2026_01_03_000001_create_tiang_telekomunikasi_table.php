<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel utama sistem — data tiang telekomunikasi.
     *
     * CATATAN KODE TIANG:
     * Auto-generate menggunakan DB transaction + SELECT...FOR UPDATE (di application layer).
     * Format: TI-{STO_KODE}-{NNNNN}, contoh: TI-KDT-00001
     *
     * CATATAN PARTIAL UNIQUE INDEX id_tiang_instansi:
     * PostgreSQL mengizinkan banyak NULL dalam unique column.
     * Index dibuat via DB::statement setelah tabel terbentuk.
     *
     * STATUS VERIFIKASI — state machine (tidak boleh dibalik sembarangan):
     *   pending → ok/ditolak/double_input (oleh admin)
     *   ditolak → pending (oleh teknisi)
     *   TIDAK boleh: ok → apapun, double_input → ok langsung
     *
     * CATATAN has_anomali:
     * READONLY — HANYA boleh diubah oleh AnomalyDetectionService via saveQuietly().
     *
     * CATATAN PostGIS (belum dieksekusi, untuk dokumentasi):
     * ALTER TABLE tiang_telekomunikasi ADD COLUMN geom geometry(Point, 4326);
     * UPDATE tiang_telekomunikasi SET geom = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326);
     * CREATE INDEX idx_tiang_geom ON tiang_telekomunikasi USING GIST(geom);
     * Kolom decimal tetap dipertahankan paralel.
     */
    public function up(): void
    {
        Schema::create('tiang_telekomunikasi', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Kode tiang — auto-generate, nullable karena bisa diisi setelah insert
            $table->string('kode_tiang', 30)->unique()->nullable();

            // ID dari sistem instansi lain — nullable dengan partial unique index
            $table->string('id_tiang_instansi', 50)->nullable();

            // FK Master Data — semuanya restrictOnDelete
            $table->foreignId('sto_id')
                  ->constrained('stos')
                  ->restrictOnDelete();
            $table->foreignId('jenis_tiang_id')
                  ->constrained('jenis_tiang')
                  ->restrictOnDelete();
            $table->foreignId('kondisi_tiang_id')
                  ->constrained('kondisi_tiang')
                  ->restrictOnDelete();

            // Koordinat GIS
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            // Data fisik tiang
            $table->text('nama_jalan');
            $table->integer('jml_tiang_operator_sekitar')->default(0);
            $table->integer('jml_kabel_dc_telkom')->default(0);
            $table->integer('jml_ku_telkom')->default(0);

            // Data operasional
            $table->string('nama_teknisi', 200)->nullable();
            $table->date('tgl_input');
            $table->date('tanggal_temuan')->nullable();

            // Status verifikasi — state machine (enforced di Controller/Service)
            $table->enum('status_verifikasi', ['pending', 'ok', 'double_input', 'ditolak'])
                  ->default('pending');

            // Flag anomali — READONLY, hanya AnomalyDetectionService yang boleh ubah
            $table->boolean('has_anomali')->default(false);

            // Audit trail — nullOnDelete agar tiang tidak ikut terhapus saat user dihapus
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            // === INDEX ===
            $table->index('sto_id');
            $table->index('jenis_tiang_id');
            $table->index('kondisi_tiang_id');
            $table->index('status_verifikasi');
            $table->index('has_anomali');
            $table->index('tgl_input');
            $table->index('deleted_at');
            $table->index(['latitude', 'longitude'], 'idx_tiang_lat_lng');
        });

        // === PARTIAL UNIQUE INDEX: id_tiang_instansi IS NOT NULL ===
        // PostgreSQL izinkan banyak NULL di unique column, tapi hanya satu nilai non-NULL
        DB::statement("
            CREATE UNIQUE INDEX idx_tiang_instansi_notnull
            ON tiang_telekomunikasi (id_tiang_instansi)
            WHERE id_tiang_instansi IS NOT NULL
        ");

        // === CHECK CONSTRAINTS via DB::statement ===
        DB::statement("
            ALTER TABLE tiang_telekomunikasi
            ADD CONSTRAINT chk_tiang_latitude
            CHECK (latitude BETWEEN -7.0 AND -4.0)
        ");

        DB::statement("
            ALTER TABLE tiang_telekomunikasi
            ADD CONSTRAINT chk_tiang_longitude
            CHECK (longitude BETWEEN 104.0 AND 107.0)
        ");

        DB::statement("
            ALTER TABLE tiang_telekomunikasi
            ADD CONSTRAINT chk_tiang_jml_operator
            CHECK (jml_tiang_operator_sekitar >= 0)
        ");

        DB::statement("
            ALTER TABLE tiang_telekomunikasi
            ADD CONSTRAINT chk_tiang_jml_kabel_dc
            CHECK (jml_kabel_dc_telkom >= 0)
        ");

        DB::statement("
            ALTER TABLE tiang_telekomunikasi
            ADD CONSTRAINT chk_tiang_jml_ku
            CHECK (jml_ku_telkom >= 0)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiang_telekomunikasi');
    }
};
