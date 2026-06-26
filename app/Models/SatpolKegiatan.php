<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatpolKegiatan extends Model
{
    use HasFactory;

    protected $table = 'satpol_kegiatans';

    protected $fillable = [
        'no_kegiatan',
        'tanggal_kegiatan',
        'bidang',
        'jenis_kegiatan',
        'lokasi',
        'jumlah_personel',
        'uraian_kegiatan',
        'foto_bukti',
    ];

    protected $casts = [
        'tanggal_kegiatan' => 'datetime',
        'jumlah_personel' => 'integer',
    ];
}
