<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satlinmas extends Model
{
    use HasFactory;

    protected $table = 'satlinmas';

    protected $fillable = [
        'kecamatan',
        'desa',
        'anggota_pria',
        'anggota_wanita',
        'nama_kades',
        'nama_kasi',
        'kontak_perangkat',
        'jumlah_pos_kamling',
        'status_pakaian_dinas',
        'ket_pakaian_dinas',
        'jumlah_senter',
        'jumlah_pentungan',
        'jumlah_ht',
        'anggaran_honor',
        'status_sk_satlinmas',
        'peraturan_desa',
        'status_struktur',
        'pelatihan_anggota',
        'status_kta',
        'petugas_pendata',
        'tanggal_pendataan',
    ];

    protected $casts = [
        'anggota_pria' => 'integer',
        'anggota_wanita' => 'integer',
        'jumlah_pos_kamling' => 'integer',
        'jumlah_senter' => 'integer',
        'jumlah_pentungan' => 'integer',
        'jumlah_ht' => 'integer',
        'anggaran_honor' => 'double',
        'tanggal_pendataan' => 'datetime',
    ];
}
