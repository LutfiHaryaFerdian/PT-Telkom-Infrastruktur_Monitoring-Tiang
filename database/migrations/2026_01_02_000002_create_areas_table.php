<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel areas: sub-wilayah di bawah district — TIDAK SoftDeletes.
     * FK areas.district_id → restrictOnDelete (tolak hapus district jika masih ada area).
     * UNIQUE (district_id, name): tidak boleh duplikat nama area dalam satu district.
     */
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('district_id')
                  ->constrained('districts')
                  ->restrictOnDelete();
            $table->string('name', 100);
            $table->timestamps();

            $table->unique(['district_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
