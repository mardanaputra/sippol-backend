<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenertibanK3 extends Model
{
    use HasFactory;

    protected $table = 'penertiban_k3s';

    protected $fillable = [
        'no_formulir',
        'id_tiket',
        'no_spt',
        'tanggal_kejadian',
        'lokasi',
        'latitude',
        'longitude',
        'jenis_pelanggaran',
        'nama_pelanggar',
        'tindakan_diambil',
        'jumlah_pelanggar',
        'keterangan',
        'foto_bukti',
    ];

    protected $casts = [
        'tanggal_kejadian' => 'datetime',
        'jumlah_pelanggar' => 'integer',
    ];

    /**
     * Get the patrol squad associated with this enforcement log.
     */
    public function patroli(): BelongsTo
    {
        return $this->belongsTo(ReguPatroli::class, 'no_spt', 'no_spt');
    }

    /**
     * Get the complaint associated with this enforcement log.
     */
    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class, 'id_tiket', 'id_tiket');
    }
}
