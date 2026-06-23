<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use Illuminate\Http\Request;

class PengaduanController extends Controller
{
    /**
     * Display a listing of complaints with their dispositions (Admin only).
     */
    public function index()
    {
        $reports = Pengaduan::with('disposisi')
            ->orderBy('waktu_kirim', 'desc')
            ->get();

        return response()->json($reports, 200);
    }

    /**
     * Store a newly created complaint in storage (Public endpoint).
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_tiket' => 'required|string|unique:pengaduans,id_tiket',
            'nama_pelapor' => 'required|string',
            'is_anonim' => 'boolean',
            'nomor_whatsapp' => 'required|string',
            'kategori_masalah' => 'required|string',
            'kronologi' => 'required|string',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            'foto_bukti' => 'nullable|string', // Base64 image
        ]);

        $report = Pengaduan::create([
            'id_tiket' => $validatedData['id_tiket'],
            'nama_pelapor' => $validatedData['nama_pelapor'],
            'is_anonim' => $validatedData['is_anonim'] ?? false,
            'nomor_whatsapp' => $validatedData['nomor_whatsapp'],
            'kategori_masalah' => $validatedData['kategori_masalah'],
            'kronologi' => $validatedData['kronologi'],
            'latitude' => $validatedData['latitude'],
            'longitude' => $validatedData['longitude'],
            'foto_bukti' => $validatedData['foto_bukti'] ?? null,
            'status_laporan' => 'Pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil disimpan.',
            'data' => $report
        ], 201);
    }

    /**
     * Display the specified complaint with its disposition (Public or Admin).
     */
    public function show(string $id)
    {
        $report = Pengaduan::with('disposisi')->find($id);

        if (!$report) {
            return response()->json([
                'error' => 'Tiket pengaduan tidak ditemukan.'
            ], 404);
        }

        return response()->json($report, 200);
    }

    /**
     * Remove the specified complaint from storage (Admin only).
     */
    public function destroy(string $id)
    {
        $report = Pengaduan::find($id);

        if (!$report) {
            return response()->json([
                'error' => 'Tiket pengaduan tidak ditemukan.'
            ], 404);
        }

        $report->delete();

        return response()->json([
            'success' => true,
            'message' => "Laporan {$id} berhasil dihapus."
        ], 200);
    }
}
