<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel operator_isp: master ISP/operator penumpang — memiliki SoftDeletes.
     * is_predefined=true untuk operator bawaan sistem (BIZNET, ICON+, dsb).
     *
     * ATURAN HAPUS:
     * Sebelum soft delete, cek tiang_operator dengan operator_id ini yang tiang-nya masih aktif.
     * Jika ada → tolak soft delete (ditangani di Controller/Service).
     */
    public function up(): void
    {
        Schema::create('operator_isp', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama_operator', 100)->unique();
            $table->boolean('is_predefined')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index('is_predefined');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_isp');
    }
};
