<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReguPatroli;
use Illuminate\Http\Request;

class ReguPatroliController extends Controller
{
    /**
     * Display a listing of patrol schedules.
     */
    public function index()
    {
        $patrols = ReguPatroli::orderBy('tanggal_penugasan', 'desc')->get();
        return response()->json($patrols, 200);
    }

    /**
     * Store a newly created patrol schedule.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal_penugasan' => 'nullable|date',
            'shift_kerja' => 'required|string',
            'komandan_regu' => 'required|string',
            'anggota_regu' => 'required', // Can be string or array
            'wilayah_patroli' => 'required', // Can be string or array
            'keterangan_area' => 'nullable|string',
            'kendaraan_dinas' => 'required|string',
            'surat_tugas' => 'nullable|string',
        ]);

        $anggotaStr = is_array($request->anggota_regu) 
            ? implode(', ', $request->anggota_regu) 
            : $request->anggota_regu;

        $wilayahStr = is_array($request->wilayah_patroli) 
            ? implode(', ', $request->wilayah_patroli) 
            : $request->wilayah_patroli;

        $dateObj = $request->tanggal_penugasan ? new \DateTime($request->tanggal_penugasan) : now();
        $currentYear = $dateObj->format('Y');
        $prefix = "SPT/TRANTIB/{$currentYear}/";

        // Get latest record to auto-generate sequence
        $latest = ReguPatroli::where('no_spt', 'like', $prefix . '%')
            ->orderBy('no_spt', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latest) {
            $parts = explode('/', $latest->no_spt);
            $lastNum = intval(end($parts));
            if ($lastNum > 0) {
                $nextNumber = $lastNum + 1;
            }
        }
        $no_spt = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $patrol = ReguPatroli::create([
            'no_spt' => $no_spt,
            'tanggal_penugasan' => $dateObj,
            'shift_kerja' => $request->shift_kerja,
            'komandan_regu' => $request->komandan_regu,
            'anggota_regu' => $anggotaStr,
            'wilayah_patroli' => $wilayahStr,
            'keterangan_area' => $request->keterangan_area,
            'kendaraan_dinas' => $request->kendaraan_dinas,
            'surat_tugas' => $request->surat_tugas,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Regu patroli berhasil di-plotting.',
            'data' => $patrol
        ], 201);
    }

    /**
     * Update the specified patrol schedule.
     */
    public function update(Request $request, $id = null)
    {
        $patrolId = $id ?: $request->id;

        if (!$patrolId) {
            return response()->json([
                'error' => 'ID data wajib disertakan untuk melakukan update.'
            ], 400);
        }

        $patrol = ReguPatroli::find($patrolId);
        if (!$patrol) {
            return response()->json([
                'error' => 'Data regu patroli tidak ditemukan.'
            ], 404);
        }

        $request->validate([
            'tanggal_penugasan' => 'nullable|date',
            'shift_kerja' => 'required|string',
            'komandan_regu' => 'required|string',
            'anggota_regu' => 'required',
            'wilayah_patroli' => 'required',
            'keterangan_area' => 'nullable|string',
            'kendaraan_dinas' => 'required|string',
            'surat_tugas' => 'nullable|string',
        ]);

        $anggotaStr = is_array($request->anggota_regu) 
            ? implode(', ', $request->anggota_regu) 
            : $request->anggota_regu;

        $wilayahStr = is_array($request->wilayah_patroli) 
            ? implode(', ', $request->wilayah_patroli) 
            : $request->wilayah_patroli;

        $patrol->update([
            'tanggal_penugasan' => $request->tanggal_penugasan ? new \DateTime($request->tanggal_penugasan) : $patrol->tanggal_penugasan,
            'shift_kerja' => $request->shift_kerja,
            'komandan_regu' => $request->komandan_regu,
            'anggota_regu' => $anggotaStr,
            'wilayah_patroli' => $wilayahStr,
            'keterangan_area' => $request->keterangan_area,
            'kendaraan_dinas' => $request->kendaraan_dinas,
            'surat_tugas' => $request->surat_tugas,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Regu patroli berhasil diperbarui.',
            'data' => $patrol
        ], 200);
    }

    /**
     * Remove the specified patrol schedule.
     */
    public function destroy(Request $request, $id = null)
    {
        $patrolId = $id ?: $request->query('id');

        if (!$patrolId) {
            return response()->json([
                'error' => 'Parameter id wajib disertakan.'
            ], 400);
        }

        $patrol = ReguPatroli::find($patrolId);
        if (!$patrol) {
            return response()->json([
                'error' => 'Data regu patroli tidak ditemukan.'
            ], 404);
        }

        $patrol->delete();

        return response()->json([
            'success' => true,
            'message' => 'Regu patroli berhasil dihapus.'
        ], 200);
    }
}
