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
        Schema::create('penertiban_trantibums', function (Blueprint $table) {
            $table->id();
            $table->string('id_tiket')->nullable();
            $table->timestamp('tanggal_ditemukan')->useCurrent();
            $table->string('lokasi_ditemukan');
            $table->string('nama_pelaku')->default('Tanpa Nama');
            $table->string('alamat_asal')->nullable();
            $table->string('jenis_kelamin');
            $table->string('status_identitas');
            $table->string('no_ktp')->default('-');
            $table->string('kategori_masalah'); // Comma-separated categories
            $table->string('no_rekam_medis')->default('Nihil');
            $table->text('keterangan_penanganan');
            $table->timestamps();

            $table->foreign('id_tiket')
                  ->references('id_tiket')
                  ->on('pengaduans')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penertiban_trantibums');
    }
};
