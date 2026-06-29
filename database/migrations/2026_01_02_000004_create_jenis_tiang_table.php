<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel jenis_tiang: master jenis tiang telekomunikasi — TIDAK SoftDeletes.
     * Contoh: T-7-2 Seqment, T-7-3 Seqment.
     */
    public function up(): void
    {
        Schema::create('jenis_tiang', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama', 100)->unique();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_tiang');
    }
};
