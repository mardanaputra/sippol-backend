<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KegiatanLinmas;
use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KegiatanLinmasController extends Controller
{
    /**
     * Display a listing of Linmas activities.
     */
    public function index(Request $request)
    {
        $type = $request->query('type');

        if ($type === 'delegated') {
            $delegatedReports = Pengaduan::with('disposisi')
                ->where('status_laporan', 'Disposisi')
                ->where('bidang_disposisi', 'Bidang Linmas')
                ->orderBy('waktu_kirim', 'desc')
                ->get();

            return response()->json($delegatedReports, 200);
        }

        $activities = KegiatanLinmas::orderBy('tanggal_kegiatan', 'desc')->get();
        return response()->json($activities, 200);
    }

    /**
     * Store a newly created Linmas activity.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_tiket' => 'nullable|string',
            'tanggal_kegiatan' => 'nullable|date',
            'kecamatan' => 'required|string',
            'desa' => 'required|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'jenis_kegiatan' => 'required|string',
            'uraian_kegiatan' => 'required|string',
            'jumlah_personel' => 'nullable|integer',
            'foto_kegiatan' => 'nullable|string', // Base64
            'selesaikan_aduan' => 'nullable|boolean',
        ]);

        try {
            $result = DB::transaction(function () use ($request) {
                $activity = KegiatanLinmas::create([
                    'id_tiket' => $request->id_tiket ?: null,
                    'tanggal_kegiatan' => $request->tanggal_kegiatan ? new \DateTime($request->tanggal_kegiatan) : now(),
                    'kecamatan' => $request->kecamatan,
                    'desa' => $request->desa,
                    'latitude' => $request->latitude ?: null,
                    'longitude' => $request->longitude ?: null,
                    'jenis_kegiatan' => $request->jenis_kegiatan,
                    'uraian_kegiatan' => $request->uraian_kegiatan,
                    'jumlah_personel' => intval($request->jumlah_personel) ?: 1,
                    'foto_kegiatan' => $request->foto_kegiatan ?: null,
                ]);

                if ($request->id_tiket && $request->selesaikan_aduan) {
                    Pengaduan::where('id_tiket', $request->id_tiket)
                        ->update(['status_laporan' => 'Selesai']);
                }

                return $activity;
            });

            return response()->json([
                'success' => true,
                'message' => 'Kegiatan Linmas berhasil disimpan.',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan server saat menyimpan kegiatan.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified Linmas activity.
     */
    public function update(Request $request, $id = null)
    {
        $activityId = $id ?: $request->id;

        if (!$activityId) {
            return response()->json([
                'error' => 'ID data wajib disertakan untuk melakukan update.'
            ], 400);
        }

        $activity = KegiatanLinmas::find($activityId);
        if (!$activity) {
            return response()->json([
                'error' => 'Kegiatan Linmas tidak ditemukan.'
            ], 404);
        }

        $request->validate([
            'id_tiket' => 'nullable|string',
            'tanggal_kegiatan' => 'nullable|date',
            'kecamatan' => 'required|string',
            'desa' => 'required|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'jenis_kegiatan' => 'required|string',
            'uraian_kegiatan' => 'required|string',
            'jumlah_personel' => 'nullable|integer',
            'foto_kegiatan' => 'nullable|string',
        ]);

        $activity->update([
            'id_tiket' => $request->id_tiket ?: null,
            'tanggal_kegiatan' => $request->tanggal_kegiatan ? new \DateTime($request->tanggal_kegiatan) : $activity->tanggal_kegiatan,
            'kecamatan' => $request->kecamatan,
            'desa' => $request->desa,
            'latitude' => $request->latitude ?: null,
            'longitude' => $request->longitude ?: null,
            'jenis_kegiatan' => $request->jenis_kegiatan,
            'uraian_kegiatan' => $request->uraian_kegiatan,
            'jumlah_personel' => intval($request->jumlah_personel) ?: 1,
            'foto_kegiatan' => $request->foto_kegiatan ?: null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kegiatan Linmas berhasil diperbarui.',
            'data' => $activity
        ], 200);
    }

    /**
     * Remove the specified Linmas activity.
     */
    public function destroy(Request $request, $id = null)
    {
        $activityId = $id ?: $request->query('id');

        if (!$activityId) {
            return response()->json([
                'error' => 'Parameter id wajib disertakan.'
            ], 400);
        }

        $activity = KegiatanLinmas::find($activityId);
        if (!$activity) {
            return response()->json([
                'error' => 'Kegiatan Linmas tidak ditemukan.'
            ], 404);
        }

        $activity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kegiatan Linmas berhasil dihapus.'
        ], 200);
    }
}
