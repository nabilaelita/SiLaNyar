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
        Schema::create('hasil_uji_histori', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_hasil_uji')->constrained('hasil_uji')->onDelete('cascade');
            $table->json('data_parameterdanpengujian');
            $table->enum('status', ['draf', 'revisi', 'proses_review', 'proses_peresmian', 'selesai'])->default('draf');
            $table->string('diupdate_oleh');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_uji_histori');
    }
};
