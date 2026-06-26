<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SdaPustaka extends Model
{
    use HasFactory;

    protected $table = 'sda_pustakas';

    protected $fillable = [
        'no_arsip',
        'judul_dokumen',
        'jenis_aturan',
        'nomor_tahun_aturan',
        'instansi_penerbit',
        'status_dokumen',
        'ringkasan_aturan',
        'tags',
        'berkas_pdf',
        'pengunggah',
        'waktu_upload',
    ];

    protected $casts = [
        'waktu_upload' => 'datetime',
    ];
}
