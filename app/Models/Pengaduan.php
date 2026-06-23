<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pengaduan extends Model
{
    use HasFactory;

    protected $table = 'pengaduans';

    // Since id_tiket is a string primary key
    protected $primaryKey = 'id_tiket';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_tiket',
        'nama_pelapor',
        'is_anonim',
        'nomor_whatsapp',
        'kategori_masalah',
        'kronologi',
        'latitude',
        'longitude',
        'foto_bukti',
        'status_laporan',
        'bidang_disposisi',
        'waktu_kirim',
    ];

    protected $casts = [
        'is_anonim' => 'boolean',
        'waktu_kirim' => 'datetime',
    ];

    /**
     * Get the disposisi associated with the pengaduan.
     */
    public function disposisi(): HasOne
    {
        return $this->hasOne(Disposisi::class, 'id_tiket', 'id_tiket');
    }
}
