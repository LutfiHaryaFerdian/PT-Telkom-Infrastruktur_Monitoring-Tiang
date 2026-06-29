<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel foto_tiang: foto permanen 3 sisi tiang (depan, kanan, kiri).
     *
     * FK:
     *   foto_tiang.tiang_id    → cascadeOnDelete
     *   foto_tiang.uploaded_by → nullOnDelete
     *
     * UNIQUE (tiang_id, jenis_foto): satu tiang hanya 1 foto per jenis.
     * Path: storage/app/public/foto_tiang/{tiang_id}/{jenis}.{ext_asli}
     *
     * CATATAN:
     * - Saat timpa: Storage::delete($old->path_file) dulu, baru simpan baru.
     * - Soft delete tiang: file TIDAK ikut dihapus (untuk restore).
     * - Hard delete/purge: file baru dihapus bersama record.
     */
    public function up(): void
    {
        Schema::create('foto_tiang', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('tiang_id')
                  ->constrained('tiang_telekomunikasi')
                  ->cascadeOnDelete();

            $table->enum('jenis_foto', ['depan', 'kanan', 'kiri']);

            // Path dengan ekstensi ASLI (jpg/png/jpeg), jangan paksa .jpg
            $table->string('path_file', 500);
            $table->string('original_filename', 255);
            $table->string('mime_type', 50);

            $table->foreignId('uploaded_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            // Satu tiang hanya 1 foto per jenis
            $table->unique(['tiang_id', 'jenis_foto']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foto_tiang');
    }
};
