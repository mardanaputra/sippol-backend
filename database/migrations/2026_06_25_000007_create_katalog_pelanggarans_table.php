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
        Schema::create('katalog_pelanggarans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_regulasi');
            $table->string('pasal');
            $table->text('jenis_pelanggaran');
            $table->string('sanksi_maksimal')->default('Denda');
            $table->double('denda_maksimal');
            $table->timestamps();

            // Foreign key to perda_perbups on kode_regulasi
            $table->foreign('kode_regulasi')
                  ->references('kode_regulasi')
                  ->on('perda_perbups')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('katalog_pelanggarans');
    }
};
