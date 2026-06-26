<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SdaPersonel extends Model
{
    use HasFactory;

    protected $table = 'sda_personels';

    protected $fillable = [
        'id_personel',
        'nip_kontrak',
        'nama_lengkap',
        'status_kepegawaian',
        'pangkat_golongan',
        'jabatan',
        'penempatan_bidang',
        'rekam_pelatihan',
        'nomor_sertifikat',
        'status_keaktifan',
    ];
}
