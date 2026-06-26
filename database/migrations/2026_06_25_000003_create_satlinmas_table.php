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
        Schema::create('satlinmas', function (Blueprint $table) {
            $table->id();
            $table->string('kecamatan');
            $table->string('desa');
            $table->integer('anggota_pria')->default(0);
            $table->integer('anggota_wanita')->default(0);
            $table->string('nama_kades');
            $table->string('nama_kasi');
            $table->string('kontak_perangkat');
            $table->integer('jumlah_pos_kamling')->default(0);
            $table->string('status_pakaian_dinas')->default('Tidak Ada');
            $table->string('ket_pakaian_dinas')->nullable();
            $table->integer('jumlah_senter')->default(0);
            $table->integer('jumlah_pentungan')->default(0);
            $table->integer('jumlah_ht')->default(0);
            $table->double('anggaran_honor')->default(0.0);
            $table->string('status_sk_satlinmas')->default('Tidak Ada');
            $table->string('peraturan_desa')->nullable();
            $table->string('status_struktur')->default('Tidak Ada');
            $table->string('pelatihan_anggota')->nullable();
            $table->string('status_kta')->default('Tidak Ada');
            $table->string('petugas_pendata');
            $table->timestamp('tanggal_pendataan')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satlinmas');
    }
};
