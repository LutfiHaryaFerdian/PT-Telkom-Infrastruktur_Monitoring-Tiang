<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel activity_logs: audit trail semua perubahan di sistem.
     *
     * FK:
     *   activity_logs.user_id → nullOnDelete (nullable: bisa oleh sistem/job)
     *
     * ALIAS model_type (gunakan ALIAS TETAP, bukan FQCN):
     *   'tiang', 'inspection', 'foto_tiang', 'foto_inspeksi',
     *   'operator_isp', 'tiang_operator', 'sto', 'import', 'anomali'
     *
     * Nilai action yang valid:
     *   created, updated, deleted, restored, verified, rejected,
     *   kode_updated, legalitas_updated, koordinat_applied, purged
     *
     * FORMAT DESCRIPTION STANDAR:
     *   "{Role} {Nama} {aksi} {entitas} {identifier} [{detail opsional}]"
     *   Contoh: "Admin Budi memverifikasi tiang TI-KDT-00017"
     *           "Sistem mendeteksi anomali double_input pada TI-KDT-00031"
     *
     * WAJIB: perubahan via API dan via web sama-sama wajib mencatat ke activity_logs.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Alias tetap, bukan FQCN (App\Models\Tiang...) — lebih bersih dan tidak berubah saat refactor
            $table->string('model_type', 50);
            $table->unsignedBigInteger('model_id');
            $table->string('action', 50);

            // Format: "{Role} {Nama} {aksi} {entitas} {identifier}"
            $table->string('description', 255);

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            // Hanya created_at (tidak perlu updated_at untuk log)
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
