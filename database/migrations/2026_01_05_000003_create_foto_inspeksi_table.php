<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel foto_inspeksi: foto pendukung hasil inspeksi lapangan.
     *
     * FK:
     *   foto_inspeksi.inspection_id → cascadeOnDelete
     *   foto_inspeksi.uploaded_by   → nullOnDelete
     *
     * BATAS 10 FOTO:
     * Validasi di FotoInspeksiRequest DAN dicek ulang di Controller:
     *   $existing = FotoInspeksi::where('inspection_id', $id)->count();
     *   if ($existing + count($files) > 10) abort(422, 'Maksimal 10 foto per inspeksi');
     *
     * Path: storage/app/public/foto_inspeksi/{inspection_id}/{uuid}.{ext_asli}
     */
    public function up(): void
    {
        Schema::create('foto_inspeksi', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('inspection_id')
                  ->constrained('inspections')
                  ->cascadeOnDelete();

            // nullable: bisa tanpa label jenis foto
            $table->string('jenis_foto', 50)->nullable();

            $table->string('path_file', 500);
            $table->string('original_filename', 255);
            $table->string('mime_type', 50);

            $table->foreignId('uploaded_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index('inspection_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foto_inspeksi');
    }
};
