<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReguPatroli extends Model
{
    use HasFactory;

    protected $table = 'regu_patrolis';

    protected $fillable = [
        'no_spt',
        'tanggal_penugasan',
        'shift_kerja',
        'komandan_regu',
        'anggota_regu',
        'wilayah_patroli',
        'keterangan_area',
        'kendaraan_dinas',
        'surat_tugas',
    ];

    protected $casts = [
        'tanggal_penugasan' => 'datetime',
    ];

    /**
     * Get the penertiban records associated with this patrol squad.
     */
    public function penertiban(): HasMany
    {
        return $this->hasMany(PenertibanK3::class, 'no_spt', 'no_spt');
    }
}
