<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Disposisi;
use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DisposisiController extends Controller
{
    /**
     * Display a listing of dispositions (Admin only).
     */
    public function index(Request $request)
    {
        $id_tiket = $request->query('id_tiket');

        if ($id_tiket) {
            $disposisi = Disposisi::where('id_tiket', $id_tiket)->first();

            if (!$disposisi) {
                return response()->json([
                    'message' => 'Belum ada disposisi untuk tiket ini.'
                ], 404);
            }

            return response()->json($disposisi, 200);
        }

        $allDispositions = Disposisi::orderBy('waktu_dikirim', 'desc')->get();
        return response()->json($allDispositions, 200);
    }

    /**
     * Store a newly created disposition and update complaint status (Admin only).
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_tiket' => 'required|string',
            'nama_admin' => 'required|string',
            'waktu_verifikasi' => 'nullable|date',
            'bidang_tujuan' => 'required|string',
            'kedaruratan' => 'required|string',
            'catatan' => 'required|string',
        ]);

        $id_tiket = $request->id_tiket;

        // Check if complaint exists
        $pengaduan = Pengaduan::find($id_tiket);
        if (!$pengaduan) {
            return response()->json([
                'error' => 'Tiket pengaduan tidak ditemukan.'
            ], 404);
        }

        // Check if disposition already exists for this ticket
        $existingDisposisi = Disposisi::where('id_tiket', $id_tiket)->first();
        if ($existingDisposisi) {
            return response()->json([
                'error' => 'Laporan dengan tiket ini sudah pernah didisposisikan sebelumnya.'
            ], 400);
        }

        try {
            $result = DB::transaction(function () use ($request, $pengaduan) {
                // 1. Create disposition
                $disposisi = Disposisi::create([
                    'id_tiket' => $request->id_tiket,
                    'nama_admin' => $request->nama_admin,
                    'waktu_verifikasi' => $request->waktu_verifikasi ? new \DateTime($request->waktu_verifikasi) : now(),
                    'bidang_tujuan' => $request->bidang_tujuan,
                    'kedaruratan' => $request->kedaruratan,
                    'catatan' => $request->catatan,
                    'waktu_dikirim' => now(),
                ]);

                // 2. Update Pengaduan status and bidang
                $pengaduan->update([
                    'status_laporan' => 'Disposisi',
                    'bidang_disposisi' => $request->bidang_tujuan,
                ]);

                return $disposisi;
            });

            return response()->json([
                'success' => true,
                'message' => 'Disposisi laporan berhasil dikirim dan dicatat.',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan server saat memproses disposisi.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
