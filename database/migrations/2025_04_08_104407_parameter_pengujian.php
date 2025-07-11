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
        Schema::create('parameter_pengujian', function(Blueprint $table) {
            $table->id();
            $table->foreignId('id_parameter')->constrained('parameter_uji')->onDelete('cascade');
            $table->foreignId('id_pengujian')->constrained('pengujian')->onDelete('cascade');
            $table->string('nilai')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameter_pengujian');
    }
};
