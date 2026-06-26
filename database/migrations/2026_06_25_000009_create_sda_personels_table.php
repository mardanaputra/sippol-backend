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
        Schema::create('sda_personels', function (Blueprint $table) {
            $table->id();
            $table->string('id_personel')->unique(); // Format: SDA-PERS-YYYY-XXX
            $table->string('nip_kontrak');
            $table->string('nama_lengkap');
            $table->string('status_kepegawaian'); // ASN (PNS/PPPK) atau Kontrak (Non-ASN)
            $table->string('pangkat_golongan');
            $table->string('jabatan');
            $table->string('penempatan_bidang'); // Linmas, Trantib, Perada, SDA
            $table->text('rekam_pelatihan'); // Comma-separated checkbox values
            $table->string('nomor_sertifikat')->nullable();
            $table->string('status_keaktifan'); // Aktif, Cuti, Pendidikan, Pensiun/Resign
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sda_personels');
    }
};
