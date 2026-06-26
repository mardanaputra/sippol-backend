<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KatalogPelanggaran extends Model
{
    use HasFactory;

    protected $table = 'katalog_pelanggarans';

    protected $fillable = [
        'kode_regulasi',
        'pasal',
        'jenis_pelanggaran',
        'sanksi_maksimal',
        'denda_maksimal',
    ];

    protected $casts = [
        'denda_maksimal' => 'double',
    ];

    /**
     * Get the regulation that owns the violation description.
     */
    public function regulasi(): BelongsTo
    {
        return $this->belongsTo(PerdaPerbup::class, 'kode_regulasi', 'kode_regulasi');
    }
}
