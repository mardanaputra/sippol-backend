<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SdaPersonel;
use Illuminate\Http\Request;

class SdaPersonelController extends Controller
{
    /**
     * Display a listing of SDA personnel.
     */
    public function index()
    {
        $records = SdaPersonel::orderBy('created_at', 'desc')->get();
        return response()->json($records, 200);
    }

    /**
     * Store a newly created SDA personnel record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nip_kontrak' => 'required|string',
            'nama_lengkap' => 'required|string',
            'status_kepegawaian' => 'required|string', // ASN (PNS/PPPK) atau Kontrak (Non-ASN)
            'pangkat_golongan' => 'required|string',
            'jabatan' => 'required|string',
            'penempatan_bidang' => 'required|string',
            'rekam_pelatihan' => 'required', // Can be string or array
            'nomor_sertifikat' => 'nullable|string',
            'status_keaktifan' => 'required|string',
        ]);

        $pelatihanStr = is_array($request->rekam_pelatihan) 
            ? implode(', ', $request->rekam_pelatihan) 
            : $request->rekam_pelatihan;

        $currentYear = now()->format('Y');
        $prefix = "SDA-PERS-{$currentYear}-";

        // Auto-generate id_personel
        $latest = SdaPersonel::where('id_personel', 'like', $prefix . '%')
            ->orderBy('id_personel', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latest) {
            $parts = explode('-', $latest->id_personel);
            $lastNum = intval(end($parts));
            if ($lastNum > 0) {
                $nextNumber = $lastNum + 1;
            }
        }
        $id_personel = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $personel = SdaPersonel::create([
            'id_personel' => $id_personel,
            'nip_kontrak' => $request->nip_kontrak,
            'nama_lengkap' => $request->nama_lengkap,
            'status_kepegawaian' => $request->status_kepegawaian,
            'pangkat_golongan' => $request->pangkat_golongan,
            'jabatan' => $request->jabatan,
            'penempatan_bidang' => $request->penempatan_bidang,
            'rekam_pelatihan' => $pelatihanStr,
            'nomor_sertifikat' => $request->nomor_sertifikat,
            'status_keaktifan' => $request->status_keaktifan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data personel berhasil ditambahkan.',
            'data' => $personel
        ], 201);
    }

    /**
     * Update the specified SDA personnel record.
     */
    public function update(Request $request, $id = null)
    {
        $personelId = $id ?: $request->id;

        if (!$personelId) {
            return response()->json([
                'error' => 'ID data wajib disertakan untuk melakukan update.'
            ], 400);
        }

        $personel = SdaPersonel::find($personelId);
        if (!$personel) {
            return response()->json([
                'error' => 'Data personel tidak ditemukan.'
            ], 404);
        }

        $request->validate([
            'nip_kontrak' => 'required|string',
            'nama_lengkap' => 'required|string',
            'status_kepegawaian' => 'required|string',
            'pangkat_golongan' => 'required|string',
            'jabatan' => 'required|string',
            'penempatan_bidang' => 'required|string',
            'rekam_pelatihan' => 'required',
            'nomor_sertifikat' => 'nullable|string',
            'status_keaktifan' => 'required|string',
        ]);

        $pelatihanStr = is_array($request->rekam_pelatihan) 
            ? implode(', ', $request->rekam_pelatihan) 
            : $request->rekam_pelatihan;

        $personel->update([
            'nip_kontrak' => $request->nip_kontrak,
            'nama_lengkap' => $request->nama_lengkap,
            'status_kepegawaian' => $request->status_kepegawaian,
            'pangkat_golongan' => $request->pangkat_golongan,
            'jabatan' => $request->jabatan,
            'penempatan_bidang' => $request->penempatan_bidang,
            'rekam_pelatihan' => $pelatihanStr,
            'nomor_sertifikat' => $request->nomor_sertifikat,
            'status_keaktifan' => $request->status_keaktifan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data personel berhasil diperbarui.',
            'data' => $personel
        ], 200);
    }

    /**
     * Remove the specified SDA personnel record.
     */
    public function destroy(Request $request, $id = null)
    {
        $personelId = $id ?: $request->query('id');

        if (!$personelId) {
            return response()->json([
                'error' => 'Parameter id wajib disertakan.'
            ], 400);
        }

        $personel = SdaPersonel::find($personelId);
        if (!$personel) {
            return response()->json([
                'error' => 'Data personel tidak ditemukan.'
            ], 404);
        }

        $personel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data personel berhasil dihapus.'
        ], 200);
    }
}
