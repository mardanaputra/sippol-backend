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
        Schema::create('sda_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('no_laporan')->unique(); // Format: LAK-SDA-YYYY-XXX
            $table->timestamp('tanggal_pelaksanaan');
            $table->string('nama_agenda');
            $table->string('lokasi_sasaran');
            $table->string('jenis_kegiatan');
            $table->integer('jumlah_peserta');
            $table->string('narasumber')->nullable();
            $table->text('ringkasan_materi');
            $table->longText('dokumen_spt')->nullable(); // Base64 PDF file
            $table->longText('foto_dokumentasi')->nullable(); // Base64 image
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sda_kegiatans');
    }
};
