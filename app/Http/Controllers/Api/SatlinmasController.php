<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Satlinmas;
use Illuminate\Http\Request;

class SatlinmasController extends Controller
{
    /**
     * Display a listing of Satlinmas data.
     */
    public function index()
    {
        $records = Satlinmas::orderBy('tanggal_pendataan', 'desc')->get();
        return response()->json($records, 200);
    }

    /**
     * Store a newly created Satlinmas record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kecamatan' => 'required|string',
            'desa' => 'required|string',
            'anggota_pria' => 'nullable|integer',
            'anggota_wanita' => 'nullable|integer',
            'nama_kades' => 'nullable|string',
            'nama_kasi' => 'nullable|string',
            'kontak_perangkat' => 'nullable|string',
            'jumlah_pos_kamling' => 'nullable|integer',
            'status_pakaian_dinas' => 'nullable|string',
            'ket_pakaian_dinas' => 'nullable|string',
            'jumlah_senter' => 'nullable|integer',
            'jumlah_pentungan' => 'nullable|integer',
            'jumlah_ht' => 'nullable|integer',
            'anggaran_honor' => 'nullable|numeric',
            'status_sk_satlinmas' => 'nullable|string',
            'peraturan_desa' => 'nullable|string',
            'status_struktur' => 'nullable|string',
            'pelatihan_anggota' => 'nullable|string',
            'status_kta' => 'nullable|string',
            'petugas_pendata' => 'required|string',
        ]);

        $record = Satlinmas::create([
            'kecamatan' => $request->kecamatan,
            'desa' => $request->desa,
            'anggota_pria' => intval($request->anggota_pria) ?: 0,
            'anggota_wanita' => intval($request->anggota_wanita) ?: 0,
            'nama_kades' => $request->nama_kades ?: '',
            'nama_kasi' => $request->nama_kasi ?: '',
            'kontak_perangkat' => $request->kontak_perangkat ?: '',
            'jumlah_pos_kamling' => intval($request->jumlah_pos_kamling) ?: 0,
            'status_pakaian_dinas' => $request->status_pakaian_dinas ?: 'Tidak Ada',
            'ket_pakaian_dinas' => $request->ket_pakaian_dinas,
            'jumlah_senter' => intval($request->jumlah_senter) ?: 0,
            'jumlah_pentungan' => intval($request->jumlah_pentungan) ?: 0,
            'jumlah_ht' => intval($request->jumlah_ht) ?: 0,
            'anggaran_honor' => doubleval($request->anggaran_honor) ?: 0.0,
            'status_sk_satlinmas' => $request->status_sk_satlinmas ?: 'Tidak Ada',
            'peraturan_desa' => $request->peraturan_desa,
            'status_struktur' => $request->status_struktur ?: 'Tidak Ada',
            'pelatihan_anggota' => $request->pelatihan_anggota,
            'status_kta' => $request->status_kta ?: 'Tidak Ada',
            'petugas_pendata' => $request->petugas_pendata,
            'tanggal_pendataan' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Satlinmas berhasil ditambahkan.',
            'data' => $record
        ], 201);
    }

    /**
     * Update the specified Satlinmas record.
     */
    public function update(Request $request, $id = null)
    {
        $recordId = $id ?: $request->id;

        if (!$recordId) {
            return response()->json([
                'error' => 'ID data wajib disertakan untuk melakukan update.'
            ], 400);
        }

        $record = Satlinmas::find($recordId);
        if (!$record) {
            return response()->json([
                'error' => 'Data Satlinmas tidak ditemukan.'
            ], 404);
        }

        $request->validate([
            'kecamatan' => 'required|string',
            'desa' => 'required|string',
            'anggota_pria' => 'nullable|integer',
            'anggota_wanita' => 'nullable|integer',
            'nama_kades' => 'nullable|string',
            'nama_kasi' => 'nullable|string',
            'kontak_perangkat' => 'nullable|string',
            'jumlah_pos_kamling' => 'nullable|integer',
            'status_pakaian_dinas' => 'nullable|string',
            'ket_pakaian_dinas' => 'nullable|string',
            'jumlah_senter' => 'nullable|integer',
            'jumlah_pentungan' => 'nullable|integer',
            'jumlah_ht' => 'nullable|integer',
            'anggaran_honor' => 'nullable|numeric',
            'status_sk_satlinmas' => 'nullable|string',
            'peraturan_desa' => 'nullable|string',
            'status_struktur' => 'nullable|string',
            'pelatihan_anggota' => 'nullable|string',
            'status_kta' => 'nullable|string',
            'petugas_pendata' => 'required|string',
        ]);

        $record->update([
            'kecamatan' => $request->kecamatan,
            'desa' => $request->desa,
            'anggota_pria' => $request->anggota_pria !== null ? intval($request->anggota_pria) : $record->anggota_pria,
            'anggota_wanita' => $request->anggota_wanita !== null ? intval($request->anggota_wanita) : $record->anggota_wanita,
            'nama_kades' => $request->nama_kades ?: '',
            'nama_kasi' => $request->nama_kasi ?: '',
            'kontak_perangkat' => $request->kontak_perangkat ?: '',
            'jumlah_pos_kamling' => $request->jumlah_pos_kamling !== null ? intval($request->jumlah_pos_kamling) : $record->jumlah_pos_kamling,
            'status_pakaian_dinas' => $request->status_pakaian_dinas ?: 'Tidak Ada',
            'ket_pakaian_dinas' => $request->ket_pakaian_dinas,
            'jumlah_senter' => $request->jumlah_senter !== null ? intval($request->jumlah_senter) : $record->jumlah_senter,
            'jumlah_pentungan' => $request->jumlah_pentungan !== null ? intval($request->jumlah_pentungan) : $record->jumlah_pentungan,
            'jumlah_ht' => $request->jumlah_ht !== null ? intval($request->jumlah_ht) : $record->jumlah_ht,
            'anggaran_honor' => $request->anggaran_honor !== null ? doubleval($request->anggaran_honor) : $record->anggaran_honor,
            'status_sk_satlinmas' => $request->status_sk_satlinmas ?: 'Tidak Ada',
            'peraturan_desa' => $request->peraturan_desa,
            'status_struktur' => $request->status_struktur ?: 'Tidak Ada',
            'pelatihan_anggota' => $request->pelatihan_anggota,
            'status_kta' => $request->status_kta ?: 'Tidak Ada',
            'petugas_pendata' => $request->petugas_pendata,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Satlinmas berhasil diperbarui.',
            'data' => $record
        ], 200);
    }

    /**
     * Remove the specified Satlinmas record.
     */
    public function destroy(Request $request, $id = null)
    {
        $recordId = $id ?: $request->query('id');

        if (!$recordId) {
            return response()->json([
                'error' => 'Parameter id wajib disertakan.'
            ], 400);
        }

        $record = Satlinmas::find($recordId);
        if (!$record) {
            return response()->json([
                'error' => 'Data Satlinmas tidak ditemukan.'
            ], 404);
        }

        $record->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Satlinmas berhasil dihapus.'
        ], 200);
    }
}
