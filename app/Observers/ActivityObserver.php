<?php

namespace App\Observers;

use App\Models\ActivityLog;

class ActivityObserver
{
    /**
     * Map class names to human-readable names.
     */
    protected function getModelNameAndIdentifier($model): array
    {
        $className = class_basename($model);
        
        $modelNames = [
            'User' => 'User Admin',
            'SdaPersonel' => 'Personel SDA',
            'KatalogPelanggaran' => 'Katalog Pelanggaran',
            'ReguPatroli' => 'Regu Patroli',
            'PenertibanK3' => 'Penertiban K3',
            'Satlinmas' => 'Data Satlinmas',
            'SdaKegiatan' => 'Kegiatan SDA',
            'SdaPustaka' => 'Pustaka SDA',
            'SatpolKegiatan' => 'Kegiatan Satpol PP',
            'Pengaduan' => 'Pengaduan Warga',
            'Disposisi' => 'Disposisi Laporan',
            'KegiatanLinmas' => 'Kegiatan Linmas',
            'PenertibanTrantibum' => 'Penertiban Trantibum',
            'PerdaPerbup' => 'Regulasi Perda/Perbup',
            'PenegakanPerada' => 'Penegakan Perada'
        ];
        
        $readableName = $modelNames[$className] ?? $className;

        // Try to find the best identifying field for the entity
        $identifiers = [
            'name', 
            'nama_lengkap', 
            'jenis_pelanggaran', 
            'komandan_regu', 
            'nama_kegiatan', 
            'judul_buku', 
            'jenis_kegiatan', 
            'id_tiket', 
            'desa', 
            'kecamatan', 
            'username', 
            'nip_kontrak', 
            'pasal'
        ];

        $identifierValue = 'ID ' . $model->id;
        foreach ($identifiers as $field) {
            if (isset($model->$field)) {
                $identifierValue = $model->$field;
                break;
            }
        }

        return [$readableName, $identifierValue];
    }

    /**
     * Handle the Model "created" event.
     */
    public function created($model): void
    {
        if ($model instanceof ActivityLog) {
            return;
        }

        $userId = auth()->id();
        list($modelName, $identifier) = $this->getModelNameAndIdentifier($model);

        ActivityLog::create([
            'user_id' => $userId,
            'action' => 'TAMBAH_DATA', // Consistent with user requests: TAMBAH_DATA / TAMBAH_PERSONEL
            'description' => "Menambahkan data {$modelName} baru: {$identifier}",
            'ip_address' => request()->ip()
        ]);
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated($model): void
    {
        if ($model instanceof ActivityLog) {
            return;
        }

        $userId = auth()->id();
        list($modelName, $identifier) = $this->getModelNameAndIdentifier($model);

        $dirty = $model->getDirty();
        unset($dirty['updated_at']);

        if (empty($dirty)) {
            return;
        }

        $changes = [];
        foreach ($dirty as $key => $value) {
            $original = $model->getOriginal($key);
            $originalStr = is_array($original) ? json_encode($original) : (string)$original;
            $valueStr = is_array($value) ? json_encode($value) : (string)$value;
            $changes[] = "{$key} dari '{$originalStr}' menjadi '{$valueStr}'";
        }

        $changesDescription = implode(', ', $changes);

        ActivityLog::create([
            'user_id' => $userId,
            'action' => 'UPDATE_DATA',
            'description' => "Mengubah data {$modelName} ({$identifier}): mengubah {$changesDescription}",
            'ip_address' => request()->ip()
        ]);
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted($model): void
    {
        if ($model instanceof ActivityLog) {
            return;
        }

        $userId = auth()->id();
        list($modelName, $identifier) = $this->getModelNameAndIdentifier($model);

        ActivityLog::create([
            'user_id' => $userId,
            'action' => 'HAPUS_DATA',
            'description' => "Menghapus data {$modelName}: {$identifier}",
            'ip_address' => request()->ip()
        ]);
    }
}
