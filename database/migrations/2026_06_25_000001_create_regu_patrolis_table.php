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
        Schema::create('regu_patrolis', function (Blueprint $table) {
            $table->id();
            $table->string('no_spt')->unique(); // Format: SPT/TRANTIB/YYYY/XXX
            $table->timestamp('tanggal_penugasan')->useCurrent();
            $table->string('shift_kerja'); // Pagi, Siang, Malam
            $table->string('komandan_regu');
            $table->text('anggota_regu'); // Comma-separated list of names
            $table->text('wilayah_patroli'); // Comma-separated list of patrol routes
            $table->text('keterangan_area')->nullable();
            $table->string('kendaraan_dinas');
            $table->longText('surat_tugas')->nullable(); // Base64 PDF file
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regu_patrolis');
    }
};
