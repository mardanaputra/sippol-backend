<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SdaKegiatan extends Model
{
    use HasFactory;

    protected $table = 'sda_kegiatans';

    protected $fillable = [
        'no_laporan',
        'tanggal_pelaksanaan',
        'nama_agenda',
        'lokasi_sasaran',
        'jenis_kegiatan',
        'jumlah_peserta',
        'narasumber',
        'ringkasan_materi',
        'dokumen_spt',
        'foto_dokumentasi',
    ];

    protected $casts = [
        'tanggal_pelaksanaan' => 'datetime',
        'jumlah_peserta' => 'integer',
    ];
}
