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
        Schema::create('kegiatan_linmas', function (Blueprint $table) {
            $table->id();
            $table->string('id_tiket')->nullable();
            $table->timestamp('tanggal_kegiatan')->useCurrent();
            $table->string('kecamatan');
            $table->string('desa');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('jenis_kegiatan');
            $table->text('uraian_kegiatan');
            $table->integer('jumlah_personel')->default(1);
            $table->longText('foto_kegiatan')->nullable(); // Base64 image
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
        Schema::dropIfExists('kegiatan_linmas');
    }
};
