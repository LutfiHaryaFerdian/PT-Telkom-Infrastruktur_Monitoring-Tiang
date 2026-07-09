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
        Schema::create('isp_surat', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tiang_operator_id')
                  ->constrained('tiang_operator')
                  ->cascadeOnDelete();
            $table->string('nomor_surat', 100)->nullable();
            $table->enum('jenis_surat', ['pemberitahuan', 'peringatan', 'konfirmasi', 'tagihan', 'lainnya']);
            $table->date('tanggal_surat');
            $table->string('perihal', 255);
            $table->text('isi_ringkasan')->nullable();
            $table->string('file_surat', 500)->nullable();
            $table->foreignId('dikirim_oleh')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();

            $table->index('tiang_operator_id');
            $table->index('tanggal_surat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('isp_surat');
    }
};
