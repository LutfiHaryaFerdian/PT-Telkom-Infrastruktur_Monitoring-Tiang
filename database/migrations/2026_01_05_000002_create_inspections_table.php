<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel inspections: hasil inspeksi lapangan per tiang.
     *
     * FK:
     *   inspections.tiang_id         → cascadeOnDelete
     *   inspections.inspected_by     → nullOnDelete
     *   inspections.kondisi_tiang_id → restrictOnDelete
     *
     * KOORDINAT INSPEKSI:
     * Jika lat/lng diisi dan berbeda dari data tiang → tampilkan info di halaman show.
     * Tombol opsional "Terapkan ke data tiang" → update lat/lng tiang + catat activity_log.
     * Ini adalah aksi OPSIONAL, bukan otomatis.
     */
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('tiang_id')
                  ->constrained('tiang_telekomunikasi')
                  ->cascadeOnDelete();

            $table->foreignId('inspected_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('kondisi_tiang_id')
                  ->constrained('kondisi_tiang')
                  ->restrictOnDelete();

            // Koordinat saat inspeksi — nullable, bisa berbeda dari data tiang
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->text('catatan')->nullable();
            $table->timestamp('inspected_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
