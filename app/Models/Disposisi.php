<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disposisi extends Model
{
    use HasFactory;

    protected $table = 'disposisis';

    protected $primaryKey = 'no_urut';

    protected $fillable = [
        'id_tiket',
        'nama_admin',
        'waktu_verifikasi',
        'bidang_tujuan',
        'kedaruratan',
        'catatan',
        'waktu_dikirim',
    ];

    protected $casts = [
        'waktu_verifikasi' => 'datetime',
        'waktu_dikirim' => 'datetime',
    ];

    /**
     * Get the pengaduan that owns the disposisi.
     */
    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class, 'id_tiket', 'id_tiket');
    }
}
