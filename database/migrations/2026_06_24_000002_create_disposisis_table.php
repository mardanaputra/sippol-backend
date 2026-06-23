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
        Schema::create('disposisis', function (Blueprint $table) {
            $table->id('no_urut'); // Primary Key (autoincrement Int)
            $table->string('id_tiket')->unique();
            $table->string('nama_admin');
            $table->timestamp('waktu_verifikasi')->useCurrent();
            $table->string('bidang_tujuan');
            $table->string('kedaruratan');
            $table->text('catatan');
            $table->timestamp('waktu_dikirim')->useCurrent();
            $table->timestamps();

            // Foreign key to pengaduans table on id_tiket
            $table->foreign('id_tiket')
                  ->references('id_tiket')
                  ->on('pengaduans')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposisis');
    }
};
