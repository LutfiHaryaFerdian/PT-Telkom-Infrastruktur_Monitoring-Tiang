<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('isp_followup', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tiang_operator_id')
                  ->constrained('tiang_operator')
                  ->cascadeOnDelete();
            $table->date('tanggal_followup');
            $table->enum('metode', ['telepon', 'email', 'kunjungan_langsung', 'rapat', 'whatsapp', 'lainnya']);
            $table->text('catatan');
            $table->enum('hasil', ['berhasil_dihubungi', 'tidak_ada_respons', 'dijadwalkan_ulang', 'selesai']);
            $table->string('file_bukti', 500)->nullable();
            $table->foreignId('dilakukan_oleh')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();

            $table->index('tiang_operator_id');
            $table->index('tanggal_followup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('isp_followup');
    }
};
