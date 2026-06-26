<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SatpolKegiatan;
use Illuminate\Http\Request;

class SatpolKegiatanController extends Controller
{
    /**
     * Display a listing of activities.
     */
    public function index()
    {
        $activities = SatpolKegiatan::orderBy('tanggal_kegiatan', 'desc')->get();
        
        $mapped = $activities->map(function ($act) {
            $act->createdAt = $act->created_at;
            return $act;
        });
        
        return response()->json($mapped, 200);
    }

    /**
     * Store a newly created activity record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal_kegiatan' => 'nullable|date',
            'bidang' => 'required|string', // Linmas, Trantib, Perada, SDA
            'jenis_kegiatan' => 'required|string',
            'lokasi' => 'required|string',
            'jumlah_personel' => 'nullable|integer',
            'uraian_kegiatan' => 'required|string',
            'foto_bukti' => 'nullable|string', // Base64
        ]);

        $dateObj = $request->tanggal_kegiatan ? new \DateTime($request->tanggal_kegiatan) : now();
        $currentYear = $dateObj->format('Y');
        $bidangUpper = strtoupper(explode(' ', trim($request->bidang))[0]); // e.g. "Bidang Trantib" -> "TRANTIB"
        if (str_starts_with($bidangUpper, 'BIDANG')) {
            $parts = explode(' ', trim($request->bidang));
            $bidangUpper = isset($parts[1]) ? strtoupper($parts[1]) : strtoupper($parts[0]);
        }
        $prefix = "ACT/{$bidangUpper}/{$currentYear}/";

        // Auto-generate no_kegiatan
        $latest = SatpolKegiatan::where('no_kegiatan', 'like', $prefix . '%')
            ->orderBy('no_kegiatan', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latest) {
            $parts = explode('/', $latest->no_kegiatan);
            $lastNum = intval(end($parts));
            if ($lastNum > 0) {
                $nextNumber = $lastNum + 1;
            }
        }
        $no_kegiatan = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $activity = SatpolKegiatan::create([
            'no_kegiatan' => $no_kegiatan,
            'tanggal_kegiatan' => $dateObj,
            'bidang' => $request->bidang,
            'jenis_kegiatan' => $request->jenis_kegiatan,
            'lokasi' => $request->lokasi,
            'jumlah_personel' => intval($request->jumlah_personel) ?: 1,
            'uraian_kegiatan' => $request->uraian_kegiatan,
            'foto_bukti' => $request->foto_bukti,
        ]);

        $activity->createdAt = $activity->created_at;

        return response()->json([
            'success' => true,
            'message' => 'Kegiatan Satpol PP berhasil dicatat.',
            'data' => $activity
        ], 201);
    }

    /**
     * Update the specified activity record.
     */
    public function update(Request $request, $id = null)
    {
        $activityId = $id ?: $request->id;

        if (!$activityId) {
            return response()->json([
                'error' => 'ID data wajib disertakan untuk melakukan update.'
            ], 400);
        }

        $activity = SatpolKegiatan::find($activityId);
        if (!$activity) {
            return response()->json([
                'error' => 'Data kegiatan tidak ditemukan.'
            ], 404);
        }

        $request->validate([
            'tanggal_kegiatan' => 'nullable|date',
            'bidang' => 'required|string',
            'jenis_kegiatan' => 'required|string',
            'lokasi' => 'required|string',
            'jumlah_personel' => 'nullable|integer',
            'uraian_kegiatan' => 'required|string',
            'foto_bukti' => 'nullable|string',
        ]);

        $activity->update([
            'tanggal_kegiatan' => $request->tanggal_kegiatan ? new \DateTime($request->tanggal_kegiatan) : $activity->tanggal_kegiatan,
            'bidang' => $request->bidang,
            'jenis_kegiatan' => $request->jenis_kegiatan,
            'lokasi' => $request->lokasi,
            'jumlah_personel' => intval($request->jumlah_personel) ?: 1,
            'uraian_kegiatan' => $request->uraian_kegiatan,
            'foto_bukti' => $request->foto_bukti !== null ? $request->foto_bukti : $activity->foto_bukti,
        ]);

        $activity->createdAt = $activity->created_at;

        return response()->json([
            'success' => true,
            'message' => 'Kegiatan Satpol PP berhasil diperbarui.',
            'data' => $activity
        ], 200);
    }

    /**
     * Remove the specified activity record.
     */
    public function destroy(Request $request, $id = null)
    {
        $activityId = $id ?: $request->query('id');

        if (!$activityId) {
            return response()->json([
                'error' => 'Parameter id wajib disertakan.'
            ], 400);
        }

        $activity = SatpolKegiatan::find($activityId);
        if (!$activity) {
            return response()->json([
                'error' => 'Data kegiatan tidak ditemukan.'
            ], 404);
        }

        $activity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kegiatan Satpol PP berhasil dihapus.'
        ], 200);
    }
}
