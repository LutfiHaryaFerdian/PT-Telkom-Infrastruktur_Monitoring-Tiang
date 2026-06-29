<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel pivot tiang_operator: relasi tiang dengan ISP penumpang.
     *
     * FK:
     *   tiang_operator.tiang_id    → cascadeOnDelete
     *   tiang_operator.operator_id → restrictOnDelete
     *
     * status_legalitas dapat diubah manual oleh admin via:
     *   PATCH /api/tiang/{id}/isp/{op_id}/legalitas
     */
    public function up(): void
    {
        Schema::create('tiang_operator', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('tiang_id')
                  ->constrained('tiang_telekomunikasi')
                  ->cascadeOnDelete();

            $table->foreignId('operator_id')
                  ->constrained('operator_isp')
                  ->restrictOnDelete();

            $table->integer('jml_kabel_dc')->default(0);
            $table->integer('jml_ku')->default(0);
            $table->integer('jml_odp')->default(0);

            $table->text('keterangan_operator')->nullable();

            $table->enum('status_legalitas', ['legal', 'ilegal', 'perlu_verifikasi'])
                  ->default('perlu_verifikasi');

            $table->timestamps();

            // Satu tiang tidak boleh punya operator ISP yang sama lebih dari sekali
            $table->unique(['tiang_id', 'operator_id']);
            $table->index('operator_id');
            $table->index('status_legalitas');
        });

        // CHECK constraints via DB::statement (PostgreSQL)
        DB::statement("
            ALTER TABLE tiang_operator
            ADD CONSTRAINT chk_to_jml_kabel_dc
            CHECK (jml_kabel_dc >= 0)
        ");

        DB::statement("
            ALTER TABLE tiang_operator
            ADD CONSTRAINT chk_to_jml_ku
            CHECK (jml_ku >= 0)
        ");

        DB::statement("
            ALTER TABLE tiang_operator
            ADD CONSTRAINT chk_to_jml_odp
            CHECK (jml_odp >= 0)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiang_operator');
    }
};
