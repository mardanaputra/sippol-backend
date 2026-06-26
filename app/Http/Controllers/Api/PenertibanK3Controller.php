<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenertibanK3;
use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenertibanK3Controller extends Controller
{
    /**
     * Display a listing of K3 enforcement logs.
     */
    public function index(Request $request)
    {
        $type = $request->query('type');

        if ($type === 'delegated') {
            // Get complaints delegated to Trantibum
            $delegatedReports = Pengaduan::with('disposisi')
                ->where('status_laporan', 'Disposisi')
                ->whereIn('bidang_disposisi', ['Bidang Trantib', 'Bidang Trantibum'])
                ->orderBy('waktu_kirim', 'desc')
                ->get();

            return response()->json($delegatedReports, 200);
        }

        $logs = PenertibanK3::with('patroli')
            ->orderBy('tanggal_kejadian', 'desc')
            ->get();

        return response()->json($logs, 200);
    }

    /**
     * Store a newly created K3 enforcement log.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_tiket' => 'nullable|string',
            'no_spt' => 'nullable|string',
            'tanggal_kejadian' => 'nullable|date',
            'lokasi' => 'required|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'jenis_pelanggaran' => 'required|string',
            'nama_pelanggar' => 'required|string',
            'tindakan_diambil' => 'required|string',
            'jumlah_pelanggar' => 'nullable|integer',
            'keterangan' => 'required|string',
            'foto_bukti' => 'nullable|string', // Base64
            'selesaikan_aduan' => 'nullable|boolean',
        ]);

        $dateObj = $request->tanggal_kejadian ? new \DateTime($request->tanggal_kejadian) : now();
        $currentYear = $dateObj->format('Y');
        $prefix = "FORM-TEGURAN/TRANTIB/{$currentYear}/";

        // Auto-generate no_formulir
        $latest = PenertibanK3::where('no_formulir', 'like', $prefix . '%')
            ->orderBy('no_formulir', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latest) {
            $parts = explode('/', $latest->no_formulir);
            $lastNum = intval(end($parts));
            if ($lastNum > 0) {
                $nextNumber = $lastNum + 1;
            }
        }
        $no_formulir = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        try {
            $result = DB::transaction(function () use ($request, $no_formulir, $dateObj) {
                $log = PenertibanK3::create([
                    'no_formulir' => $no_formulir,
                    'id_tiket' => $request->id_tiket ?: null,
                    'no_spt' => $request->no_spt ?: null,
                    'tanggal_kejadian' => $dateObj,
                    'lokasi' => $request->lokasi,
                    'latitude' => $request->latitude ?: null,
                    'longitude' => $request->longitude ?: null,
                    'jenis_pelanggaran' => $request->jenis_pelanggaran,
                    'nama_pelanggar' => $request->nama_pelanggar,
                    'tindakan_diambil' => $request->tindakan_diambil,
                    'jumlah_pelanggar' => intval($request->jumlah_pelanggar) ?: 1,
                    'keterangan' => $request->keterangan,
                    'foto_bukti' => $request->foto_bukti ?: null,
                ]);

                if ($request->id_tiket && $request->selesaikan_aduan) {
                    Pengaduan::where('id_tiket', $request->id_tiket)
                        ->update(['status_laporan' => 'Selesai']);
                }

                return $log;
            });

            return response()->json([
                'success' => true,
                'message' => 'Log penertiban K3 berhasil disimpan.',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan server saat menyimpan log penertiban K3.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified K3 enforcement log.
     */
    public function update(Request $request, $id = null)
    {
        $logId = $id ?: $request->id;

        if (!$logId) {
            return response()->json([
                'error' => 'ID data wajib disertakan untuk melakukan update.'
            ], 400);
        }

        $log = PenertibanK3::find($logId);
        if (!$log) {
            return response()->json([
                'error' => 'Log penertiban K3 tidak ditemukan.'
            ], 404);
        }

        $request->validate([
            'id_tiket' => 'nullable|string',
            'no_spt' => 'nullable|string',
            'tanggal_kejadian' => 'nullable|date',
            'lokasi' => 'required|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'jenis_pelanggaran' => 'required|string',
            'nama_pelanggar' => 'required|string',
            'tindakan_diambil' => 'required|string',
            'jumlah_pelanggar' => 'nullable|integer',
            'keterangan' => 'required|string',
            'foto_bukti' => 'nullable|string',
        ]);

        $log->update([
            'id_tiket' => $request->id_tiket ?: null,
            'no_spt' => $request->no_spt ?: null,
            'tanggal_kejadian' => $request->tanggal_kejadian ? new \DateTime($request->tanggal_kejadian) : $log->tanggal_kejadian,
            'lokasi' => $request->lokasi,
            'latitude' => $request->latitude ?: null,
            'longitude' => $request->longitude ?: null,
            'jenis_pelanggaran' => $request->jenis_pelanggaran,
            'nama_pelanggar' => $request->nama_pelanggar,
            'tindakan_diambil' => $request->tindakan_diambil,
            'jumlah_pelanggar' => intval($request->jumlah_pelanggar) ?: 1,
            'keterangan' => $request->keterangan,
            'foto_bukti' => $request->foto_bukti ?: null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Log penertiban K3 berhasil diperbarui.',
            'data' => $log
        ], 200);
    }

    /**
     * Remove the specified K3 enforcement log.
     */
    public function destroy(Request $request, $id = null)
    {
        $logId = $id ?: $request->query('id');

        if (!$logId) {
            return response()->json([
                'error' => 'Parameter id wajib disertakan.'
            ], 400);
        }

        $log = PenertibanK3::find($logId);
        if (!$log) {
            return response()->json([
                'error' => 'Log penertiban K3 tidak ditemukan.'
            ], 404);
        }

        $log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Log penertiban K3 berhasil dihapus.'
        ], 200);
    }
}
