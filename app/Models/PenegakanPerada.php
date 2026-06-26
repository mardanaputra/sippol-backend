<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenegakanPerada extends Model
{
    use HasFactory;

    protected $table = 'penegakan_peradas';

    protected $fillable = [
        'no_kejadian',
        'id_tiket',
        'tanggal_tindakan',
        'nama_pelanggar',
        'nik_pelanggar',
        'alamat_pelanggar',
        'lokasi_kejadian',
        'kode_regulasi',
        'pasal_dilanggar',
        'jenis_tindakan',
        'status_sidang',
        'tanggal_sidang',
        'lokasi_sidang',
        'denda_dijatuhkan',
        'no_bukti_setor',
        'scan_dokumen',
        'bukti_setor_kas',
        'kronologi_singkat',
        'barang_bukti',
        'catatan',
    ];

    protected $casts = [
        'tanggal_tindakan' => 'datetime',
        'tanggal_sidang' => 'datetime',
        'denda_dijatuhkan' => 'double',
    ];

    /**
     * Get the complaint associated with this BAP record.
     */
    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class, 'id_tiket', 'id_tiket');
    }
}
