<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PERFORMANCE: Tambah index pada kolom yang sering difilter/di-join.
 * Migration ini HANYA menambah index — tidak mengubah kolom apapun.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── tiang_telekomunikasi ─────────────────────────────────────────
        Schema::table('tiang_telekomunikasi', function (Blueprint $table) {
            $table->index('sto_id',            'idx_tiang_sto_id');
            $table->index('jenis_tiang_id',    'idx_tiang_jenis_tiang_id');
            $table->index('kondisi_tiang_id',  'idx_tiang_kondisi_tiang_id');
            $table->index('deleted_at',        'idx_tiang_deleted_at');
            $table->index('status_verifikasi', 'idx_tiang_status_verifikasi');
            $table->index('has_anomali',       'idx_tiang_has_anomali');
            $table->index('tgl_input',         'idx_tiang_tgl_input');
            // Composite index untuk query GIS bbox (latitude + longitude)
            $table->index(['latitude', 'longitude'], 'idx_tiang_latlng');
        });

        // ── tiang_operator ───────────────────────────────────────────────
        Schema::table('tiang_operator', function (Blueprint $table) {
            $table->index('tiang_id',    'idx_tiangop_tiang_id');
            $table->index('operator_id', 'idx_tiangop_operator_id');
        });

        // ── anomali_log ──────────────────────────────────────────────────
        Schema::table('anomali_log', function (Blueprint $table) {
            $table->index('tiang_id',   'idx_anomaly_tiang_id');
            $table->index('status',     'idx_anomaly_status');
            $table->index('created_at', 'idx_anomaly_created_at');
        });

        // ── foto_tiang ───────────────────────────────────────────────────
        Schema::table('foto_tiang', function (Blueprint $table) {
            $table->index('tiang_id', 'idx_foto_tiang_tiang_id');
        });

        // ── inspections ──────────────────────────────────────────────────
        Schema::table('inspections', function (Blueprint $table) {
            $table->index('tiang_id',      'idx_inspections_tiang_id');
            $table->index('inspected_at',  'idx_inspections_inspected_at');
        });

        // ── import_history_errors ─────────────────────────────────────────
        Schema::table('import_history_errors', function (Blueprint $table) {
            $table->index('import_history_id', 'idx_imphisterr_history_id');
        });
    }

    public function down(): void
    {
        Schema::table('tiang_telekomunikasi', function (Blueprint $table) {
            $table->dropIndex('idx_tiang_sto_id');
            $table->dropIndex('idx_tiang_jenis_tiang_id');
            $table->dropIndex('idx_tiang_kondisi_tiang_id');
            $table->dropIndex('idx_tiang_deleted_at');
            $table->dropIndex('idx_tiang_status_verifikasi');
            $table->dropIndex('idx_tiang_has_anomali');
            $table->dropIndex('idx_tiang_tgl_input');
            $table->dropIndex('idx_tiang_latlng');
        });

        Schema::table('tiang_operator', function (Blueprint $table) {
            $table->dropIndex('idx_tiangop_tiang_id');
            $table->dropIndex('idx_tiangop_operator_id');
        });

        Schema::table('anomali_log', function (Blueprint $table) {
            $table->dropIndex('idx_anomaly_tiang_id');
            $table->dropIndex('idx_anomaly_status');
            $table->dropIndex('idx_anomaly_created_at');
        });

        Schema::table('foto_tiang', function (Blueprint $table) {
            $table->dropIndex('idx_foto_tiang_tiang_id');
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->dropIndex('idx_inspections_tiang_id');
            $table->dropIndex('idx_inspections_inspected_at');
        });

        Schema::table('import_history_errors', function (Blueprint $table) {
            $table->dropIndex('idx_imphisterr_history_id');
        });
    }
};
