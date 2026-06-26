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
        Schema::create('satpol_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('no_kegiatan')->unique(); // Format: ACT/BIDANG/YYYY/XXX
            $table->timestamp('tanggal_kegiatan')->useCurrent();
            $table->string('bidang'); // Linmas, Trantib, Perada, SDA
            $table->string('jenis_kegiatan');
            $table->string('lokasi');
            $table->integer('jumlah_personel')->default(1);
            $table->text('uraian_kegiatan');
            $table->longText('foto_bukti')->nullable(); // Base64 image
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satpol_kegiatans');
    }
};
