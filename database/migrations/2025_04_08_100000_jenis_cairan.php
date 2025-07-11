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
        Schema::create('jenis_cairan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_jenis_cairan')->unique();
            $table->string('nama')->unique(); // Tambahkan unique constraint
            $table->float('batas_minimum');
            $table->float('batas_maksimum')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_cairan');
    }
};
