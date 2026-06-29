<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel import_history_errors: detail error per baris saat import.
     *
     * FK:
     *   import_history_errors.import_history_id → cascadeOnDelete
     *
     * raw_data (JSON) menyimpan seluruh nilai baris sumber dalam format:
     * {
     *   "District": "Lampung",
     *   "STO": "kdT",
     *   "Koordinat Tiang": "abc,def",
     *   ...semua kolom baris tersebut...
     * }
     */
    public function up(): void
    {
        Schema::create('import_history_errors', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('import_history_id')
                  ->constrained('import_histories')
                  ->cascadeOnDelete();

            $table->integer('row_number');
            $table->string('column_name', 100)->nullable();
            $table->text('error_message');
            $table->json('raw_data')->nullable();

            $table->timestamps();

            $table->index('import_history_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_history_errors');
    }
};
