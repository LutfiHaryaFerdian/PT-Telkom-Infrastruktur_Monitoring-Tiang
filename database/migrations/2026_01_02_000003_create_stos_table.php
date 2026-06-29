<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel stos: Sentral Telepon Otomat — memiliki SoftDeletes.
     * FK stos.area_id → restrictOnDelete.
     * kode: VARCHAR 20, UNIQUE, wajib UPPERCASE (enforced di application layer).
     *
     * ATURAN HAPUS:
     * Sebelum soft delete, cek tiang_telekomunikasi dengan sto_id ini dan deleted_at IS NULL.
     * Jika ada → tolak dengan pesan error yang jelas (ditangani di Controller/Service).
     */
    public function up(): void
    {
        Schema::create('stos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('area_id')
                  ->constrained('areas')
                  ->restrictOnDelete();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100)->nullable();
            $table->softDeletes(); // Index: deleted_at
            $table->timestamps();

            // Index untuk performa query soft-delete filtering
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stos');
    }
};
