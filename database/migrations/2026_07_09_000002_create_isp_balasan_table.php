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
        Schema::create('isp_balasan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('isp_surat_id')
                  ->constrained('isp_surat')
                  ->cascadeOnDelete();
            $table->date('tanggal_balasan');
            $table->text('isi_ringkasan')->nullable();
            $table->string('file_balasan', 500)->nullable();
            $table->enum('status_balasan', ['positif', 'negatif', 'netral', 'perlu_tindaklanjut']);
            $table->foreignId('dicatat_oleh')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();

            $table->index('isp_surat_id');
            $table->index('tanggal_balasan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('isp_balasan');
    }
};
