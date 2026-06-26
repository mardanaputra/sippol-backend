<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SdaKegiatan;
use Illuminate\Http\Request;

class SdaKegiatanController extends Controller
{
    /**
     * Display a listing of SDA activities.
     */
    public function index()
    {
        $records = SdaKegiatan::orderBy('tanggal_pelaksanaan', 'desc')->get();
        return response()->json($records, 200);
    }

    /**
     * Store a newly created SDA activity.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal_pelaksanaan' => 'required|date',
            'nama_agenda' => 'required|string',
            'lokasi_sasaran' => 'required|string',
            'jenis_kegiatan' => 'required|string',
            'jumlah_peserta' => 'required|integer',
            'narasumber' => 'nullable|string',
            'ringkasan_materi' => 'required|string',
            'dokumen_spt' => 'nullable|string',
            'foto_dokumentasi' => 'nullable|string',
        ]);

        $dateObj = new \DateTime($request->tanggal_pelaksanaan);
        $currentYear = $dateObj->format('Y');
        $prefix = "LAK-SDA-{$currentYear}-";

        // Auto-generate no_laporan
        $latest = SdaKegiatan::where('no_laporan', 'like', $prefix . '%')
            ->orderBy('no_laporan', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latest) {
            $parts = explode('-', $latest->no_laporan);
            $lastNum = intval(end($parts));
            if ($lastNum > 0) {
                $nextNumber = $lastNum + 1;
            }
        }
        $no_laporan = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $activity = SdaKegiatan::create([
            'no_laporan' => $no_laporan,
            'tanggal_pelaksanaan' => $dateObj,
            'nama_agenda' => $request->nama_agenda,
            'lokasi_sasaran' => $request->lokasi_sasaran,
            'jenis_kegiatan' => $request->jenis_kegiatan,
            'jumlah_peserta' => intval($request->jumlah_peserta),
            'narasumber' => $request->narasumber,
            'ringkasan_materi' => $request->ringkasan_materi,
            'dokumen_spt' => $request->dokumen_spt,
            'foto_dokumentasi' => $request->foto_dokumentasi,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan kegiatan Bimtek/Sosialisasi berhasil disimpan.',
            'data' => $activity
        ], 201);
    }

    /**
     * Update the specified SDA activity.
     */
    public function update(Request $request, $id = null)
    {
        $activityId = $id ?: $request->id;

        if (!$activityId) {
            return response()->json([
                'error' => 'ID data wajib disertakan untuk melakukan update.'
            ], 400);
        }

        $activity = SdaKegiatan::find($activityId);
        if (!$activity) {
            return response()->json([
                'error' => 'Laporan kegiatan tidak ditemukan.'
            ], 404);
        }

        $request->validate([
            'tanggal_pelaksanaan' => 'required|date',
            'nama_agenda' => 'required|string',
            'lokasi_sasaran' => 'required|string',
            'jenis_kegiatan' => 'required|string',
            'jumlah_peserta' => 'required|integer',
            'narasumber' => 'nullable|string',
            'ringkasan_materi' => 'required|string',
            'dokumen_spt' => 'nullable|string',
            'foto_dokumentasi' => 'nullable|string',
        ]);

        $activity->update([
            'tanggal_pelaksanaan' => new \DateTime($request->tanggal_pelaksanaan),
            'nama_agenda' => $request->nama_agenda,
            'lokasi_sasaran' => $request->lokasi_sasaran,
            'jenis_kegiatan' => $request->jenis_kegiatan,
            'jumlah_peserta' => intval($request->jumlah_peserta),
            'narasumber' => $request->narasumber,
            'ringkasan_materi' => $request->ringkasan_materi,
            'dokumen_spt' => $request->dokumen_spt !== null ? $request->dokumen_spt : $activity->dokumen_spt,
            'foto_dokumentasi' => $request->foto_dokumentasi !== null ? $request->foto_dokumentasi : $activity->foto_dokumentasi,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan kegiatan berhasil diperbarui.',
            'data' => $activity
        ], 200);
    }

    /**
     * Remove the specified SDA activity.
     */
    public function destroy(Request $request, $id = null)
    {
        $activityId = $id ?: $request->query('id');

        if (!$activityId) {
            return response()->json([
                'error' => 'Parameter id wajib disertakan.'
            ], 400);
        }

        $activity = SdaKegiatan::find($activityId);
        if (!$activity) {
            return response()->json([
                'error' => 'Laporan kegiatan tidak ditemukan.'
            ], 404);
        }

        $activity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Laporan kegiatan berhasil dihapus.'
        ], 200);
    }
}
