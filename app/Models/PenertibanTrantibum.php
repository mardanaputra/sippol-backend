<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenertibanTrantibum extends Model
{
    use HasFactory;

    protected $table = 'penertiban_trantibums';

    protected $fillable = [
        'id_tiket',
        'tanggal_ditemukan',
        'lokasi_ditemukan',
        'nama_pelaku',
        'alamat_asal',
        'jenis_kelamin',
        'status_identitas',
        'no_ktp',
        'kategori_masalah',
        'no_rekam_medis',
        'keterangan_penanganan',
    ];

    protected $casts = [
        'tanggal_ditemukan' => 'datetime',
    ];

    /**
     * Get the complaint associated with this trantibum record.
     */
    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class, 'id_tiket', 'id_tiket');
    }
}
