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
        Schema::create('penertiban_k3s', function (Blueprint $table) {
            $table->id();
            $table->string('no_formulir')->unique(); // Format: FORM-TEGURAN/TRANTIB/YYYY/XXX
            $table->string('id_tiket')->nullable();
            $table->string('no_spt')->nullable();
            $table->timestamp('tanggal_kejadian')->useCurrent();
            $table->string('lokasi');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('jenis_pelanggaran');
            $table->string('nama_pelanggar');
            $table->string('tindakan_diambil');
            $table->integer('jumlah_pelanggar')->default(1);
            $table->text('keterangan');
            $table->longText('foto_bukti')->nullable(); // Base64 image
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_tiket')
                  ->references('id_tiket')
                  ->on('pengaduans')
                  ->onDelete('set null');

            $table->foreign('no_spt')
                  ->references('no_spt')
                  ->on('regu_patrolis')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penertiban_k3s');
    }
};
