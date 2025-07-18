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
        Schema::create('parameter_uji', function (Blueprint $table) {
            $table->id();
            $table->string('kode_parameter')->unique();
            $table->string('nama_parameter')->unique();
            $table->string('satuan');
            $table->integer('harga')->check('harga >= 0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameter_uji');
    }
};
