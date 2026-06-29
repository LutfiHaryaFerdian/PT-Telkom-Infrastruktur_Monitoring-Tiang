<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel anomali_log: log anomali yang terdeteksi oleh AnomalyDetectionService.
     *
     * FK:
     *   anomali_log.tiang_id    → cascadeOnDelete
     *   anomali_log.resolved_by → nullOnDelete
     *
     * PARTIAL UNIQUE INDEX:
     * Mencegah duplikat anomali jenis yang sama jika masih aktif.
     * Lebih efisien daripada cek duplikat di Service.
     * Saat insert melanggar → tangkap UniqueConstraintViolationException, abaikan.
     *
     * 6 jenis anomali:
     *   double_input, isp_tidak_teridentifikasi, kondisi_nok,
     *   verifikasi_pending, koordinat_tidak_valid, data_tidak_lengkap
     */
    public function up(): void
    {
        Schema::create('anomali_log', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('tiang_id')
                  ->constrained('tiang_telekomunikasi')
                  ->cascadeOnDelete();

            $table->enum('jenis_anomali', [
                'double_input',
                'isp_tidak_teridentifikasi',
                'kondisi_nok',
                'verifikasi_pending',
                'koordinat_tidak_valid',
                'data_tidak_lengkap',
            ]);

            $table->text('keterangan');

            $table->enum('status', ['aktif', 'resolved'])->default('aktif');

            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();

            $table->foreignId('resolved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            // Index dasar
            $table->index('status');
            $table->index('detected_at');
            $table->index(['tiang_id', 'status'], 'idx_anomali_tiang_status');
        });

        // PARTIAL UNIQUE INDEX: mencegah duplikat anomali aktif per tiang per jenis
        // Saat insert melanggar → UniqueConstraintViolationException → tangkap di Service, abaikan
        DB::statement("
            CREATE UNIQUE INDEX idx_anomali_aktif
            ON anomali_log (tiang_id, jenis_anomali)
            WHERE status = 'aktif'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anomali_log');
    }
};
