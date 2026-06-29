<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel import_histories: riwayat proses import data Excel.
     *
     * FK:
     *   import_histories.uploaded_by → nullOnDelete
     *
     * progress_percent diupdate setiap 50 baris di job.
     * Frontend polling /api/import/{id}/progress setiap 3 detik.
     */
    public function up(): void
    {
        Schema::create('import_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('filename', 255);

            $table->foreignId('uploaded_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->integer('total_rows')->default(0);
            $table->integer('success_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->integer('progress_percent')->default(0);

            $table->enum('status', ['processing', 'done', 'failed'])->default('processing');

            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_histories');
    }
};
