<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\User;

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

        if ($model instanceof User) {
            return [$readableName, $model->username];
        }

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

        if ($model instanceof User) {
            $action = 'TAMBAH USER';
            $description = "Mendaftarkan admin baru: {$identifier}";
        } else {
            $action = 'TAMBAH DATA';
            $description = "Menambahkan data {$modelName} baru: {$identifier}";
        }

        ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
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

        if ($model instanceof User) {
            if (isset($dirty['role']) && count($dirty) === 1) {
                $action = 'UBAH ROLE';
                $description = "Mengubah hak akses user {$identifier} menjadi {$model->role}";
            } else {
                $action = 'UPDATE DATA';
                $description = "Mengubah data {$modelName} ({$identifier}): mengubah {$changesDescription}";
            }
        } else {
            $action = 'UPDATE_DATA';
            $description = "Mengubah data {$modelName} ({$identifier}): mengubah {$changesDescription}";
        }

        ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
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

        if ($model instanceof User) {
            $action = 'HAPUS USER';
            $description = "Menghapus akun admin {$identifier}";
        } else {
            $action = 'HAPUS DATA';
            $description = "Menghapus data {$modelName}: {$identifier}";
        }

        ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip()
        ]);
    }
}
