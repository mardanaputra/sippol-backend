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
        Schema::create('pengaduans', function (Blueprint $table) {
            $table->string('id_tiket')->primary();
            $table->string('nama_pelapor');
            $table->boolean('is_anonim')->default(false);
            $table->string('nomor_whatsapp');
            $table->string('kategori_masalah');
            $table->text('kronologi');
            $table->string('latitude');
            $table->string('longitude');
            $table->longText('foto_bukti')->nullable(); // accommodates base64 images
            $table->string('status_laporan')->default('Pending');
            $table->string('bidang_disposisi')->nullable();
            $table->timestamp('waktu_kirim')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduans');
    }
};
