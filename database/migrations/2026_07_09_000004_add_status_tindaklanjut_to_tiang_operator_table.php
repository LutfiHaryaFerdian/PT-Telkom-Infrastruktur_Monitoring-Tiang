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
        Schema::table('tiang_operator', function (Blueprint $table) {
            $table->enum('status_tindaklanjut', [
                'belum_disurati',
                'sudah_disurati',
                'ada_balasan',
                'perlu_followup',
                'selesai'
            ])->default('belum_disurati');

            $table->timestamp('tindaklanjut_updated_at')->nullable();

            $table->index('status_tindaklanjut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tiang_operator', function (Blueprint $table) {
            $table->dropIndex(['status_tindaklanjut']);
            $table->dropColumn(['status_tindaklanjut', 'tindaklanjut_updated_at']);
        });
    }
};
