<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KatalogPelanggaran;
use Illuminate\Http\Request;

class KatalogPelanggaranController extends Controller
{
    /**
     * Display a listing of violation details.
     */
    public function index()
    {
        $records = KatalogPelanggaran::with('regulasi')
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Return with 'createdAt' compatibility
        $mapped = $records->map(function ($rec) {
            $rec->createdAt = $rec->created_at;
            return $rec;
        });
        
        return response()->json($mapped, 200);
    }

    /**
     * Store a newly created violation catalog item.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_regulasi' => 'required|string|exists:perda_perbups,kode_regulasi',
            'pasal' => 'required|string',
            'jenis_pelanggaran' => 'required|string',
            'sanksi_maksimal' => 'nullable|string',
            'denda_maksimal' => 'required|numeric',
        ]);

        $record = KatalogPelanggaran::create([
            'kode_regulasi' => $request->kode_regulasi,
            'pasal' => $request->pasal,
            'jenis_pelanggaran' => $request->jenis_pelanggaran,
            'sanksi_maksimal' => $request->sanksi_maksimal ?: 'Denda',
            'denda_maksimal' => doubleval($request->denda_maksimal),
        ]);

        $record->createdAt = $record->created_at;

        return response()->json([
            'success' => true,
            'message' => 'Detail pelanggaran berhasil disimpan.',
            'data' => $record
        ], 201);
    }

    /**
     * Remove the specified violation item.
     */
    public function destroy(Request $request, $id = null)
    {
        $recordId = $id ?: $request->query('id');

        if (!$recordId) {
            return response()->json([
                'error' => 'Parameter id wajib disertakan.'
            ], 400);
        }

        $record = KatalogPelanggaran::find($recordId);
        if (!$record) {
            return response()->json([
                'error' => 'Data detail pelanggaran tidak ditemukan.'
            ], 404);
        }

        $record->delete();

        return response()->json([
            'success' => true,
            'message' => 'Detail pelanggaran berhasil dihapus.'
        ], 200);
    }
}
