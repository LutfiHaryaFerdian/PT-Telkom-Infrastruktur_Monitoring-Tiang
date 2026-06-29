<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel kondisi_tiang: master kondisi tiang — TIDAK SoftDeletes.
     *
     * CATATAN ENUM PostgreSQL:
     * Perubahan nilai enum tidak bisa dengan ALTER TABLE biasa.
     * Gunakan: DB::statement("ALTER TYPE kondisi_level ADD VALUE 'kritis'");
     * Atau konversi ke varchar + CHECK constraint untuk fleksibilitas lebih tinggi.
     * Lihat README untuk panduan lengkap.
     */
    public function up(): void
    {
        Schema::create('kondisi_tiang', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama', 100)->unique();
            $table->enum('level', ['baik', 'perlu_perhatian', 'rusak']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kondisi_tiang');
    }
};
