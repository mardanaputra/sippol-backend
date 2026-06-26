<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerdaPerbup extends Model
{
    use HasFactory;

    protected $table = 'perda_perbups';

    protected $fillable = [
        'kode_regulasi',
        'jenis_peraturan',
        'nomor_peraturan',
        'tahun_peraturan',
        'judul_tentang',
        'berkas_pdf',
    ];

    protected $casts = [
        'tahun_peraturan' => 'integer',
    ];

    /**
     * Get the pelanggaran items associated with this regulations model.
     */
    public function pelanggaran(): HasMany
    {
        return $this->hasMany(KatalogPelanggaran::class, 'kode_regulasi', 'kode_regulasi');
    }
}
