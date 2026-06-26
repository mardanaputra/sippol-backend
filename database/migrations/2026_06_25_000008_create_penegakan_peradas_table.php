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
        Schema::create('penegakan_peradas', function (Blueprint $table) {
            $table->id();
            $table->string('no_kejadian')->unique(); // Format: BAP/PERADA/YYYY/XXX
            $table->string('id_tiket')->nullable();
            $table->timestamp('tanggal_tindakan')->useCurrent();
            $table->string('nama_pelanggar');
            $table->string('nik_pelanggar')->nullable();
            $table->text('alamat_pelanggar')->nullable();
            $table->string('lokasi_kejadian');
            $table->string('kode_regulasi');
            $table->string('pasal_dilanggar');
            $table->string('jenis_tindakan'); // Yustisial, Tipiring
            $table->string('status_sidang')->default('Penyelidikan / Pemanggilan');
            $table->timestamp('tanggal_sidang')->nullable();
            $table->string('lokasi_sidang')->nullable();
            $table->double('denda_dijatuhkan')->nullable();
            $table->string('no_bukti_setor')->nullable();
            $table->longText('scan_dokumen')->nullable(); // Base64 PDF/image
            $table->longText('bukti_setor_kas')->nullable(); // Base64 image
            $table->text('kronologi_singkat')->nullable();
            $table->text('barang_bukti')->nullable();
            $table->text('catatan');
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
        Schema::dropIfExists('penegakan_peradas');
    }
};
