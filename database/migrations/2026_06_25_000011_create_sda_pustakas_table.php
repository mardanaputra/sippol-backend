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
        Schema::create('sda_pustakas', function (Blueprint $table) {
            $table->id();
            $table->string('no_arsip')->unique(); // Format: PSTK-SDA-YYYY-XXX
            $table->string('judul_dokumen');
            $table->string('jenis_aturan');
            $table->string('nomor_tahun_aturan');
            $table->string('instansi_penerbit');
            $table->string('status_dokumen');
            $table->text('ringkasan_aturan');
            $table->string('tags')->nullable(); // Comma-separated search keywords
            $table->longText('berkas_pdf')->nullable(); // Base64 PDF file
            $table->string('pengunggah');
            $table->timestamp('waktu_upload')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sda_pustakas');
    }
};
