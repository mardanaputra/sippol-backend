<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KegiatanLinmas extends Model
{
    use HasFactory;

    protected $table = 'kegiatan_linmas';

    protected $fillable = [
        'id_tiket',
        'tanggal_kegiatan',
        'kecamatan',
        'desa',
        'latitude',
        'longitude',
        'jenis_kegiatan',
        'uraian_kegiatan',
        'jumlah_personel',
        'foto_kegiatan',
    ];

    protected $casts = [
        'tanggal_kegiatan' => 'datetime',
        'jumlah_personel' => 'integer',
    ];

    /**
     * Get the complaint associated with this linmas activity.
     */
    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class, 'id_tiket', 'id_tiket');
    }
}
