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
        Schema::create('perda_perbups', function (Blueprint $table) {
            $table->id();
            $table->string('kode_regulasi')->unique(); // Format: REG-PERDA-YYYY-XXX atau REG-PERBUP-YYYY-XXX
            $table->string('jenis_peraturan'); // Perda, Perbup/Perkada
            $table->string('nomor_peraturan');
            $table->integer('tahun_peraturan');
            $table->text('judul_tentang');
            $table->longText('berkas_pdf')->nullable(); // Base64 PDF file
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perda_perbups');
    }
};
