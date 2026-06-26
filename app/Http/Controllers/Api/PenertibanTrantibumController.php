<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenertibanTrantibum;
use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenertibanTrantibumController extends Controller
{
    /**
     * Display a listing of Trantibum enforcement logs.
     */
    public function index()
    {
        $records = PenertibanTrantibum::orderBy('tanggal_ditemukan', 'desc')->get();
        return response()->json($records, 200);
    }

    /**
     * Store a newly created Trantibum enforcement log.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_tiket' => 'nullable|string',
            'tanggal_ditemukan' => 'nullable|date',
            'lokasi_ditemukan' => 'required|string',
            'nama_pelaku' => 'required|string',
            'alamat_asal' => 'nullable|string',
            'jenis_kelamin' => 'required|string',
            'status_identitas' => 'required|string',
            'no_ktp' => 'nullable|string',
            'kategori_masalah' => 'required', // Can be string or array
            'no_rekam_medis' => 'nullable|string',
            'keterangan_penanganan' => 'nullable|string',
            'selesaikan_aduan' => 'nullable|boolean',
        ]);

        $kategoriStr = is_array($request->kategori_masalah)
            ? implode(', ', $request->kategori_masalah)
            : $request->kategori_masalah;

        $noKtp = $request->status_identitas === 'Tidak Ada' 
            ? '-' 
            : ($request->no_ktp ?: '-');

        try {
            $result = DB::transaction(function () use ($request, $kategoriStr, $noKtp) {
                $record = PenertibanTrantibum::create([
                    'id_tiket' => $request->id_tiket ?: null,
                    'tanggal_ditemukan' => $request->tanggal_ditemukan ? new \DateTime($request->tanggal_ditemukan) : now(),
                    'lokasi_ditemukan' => $request->lokasi_ditemukan,
                    'nama_pelaku' => $request->nama_pelaku ?: 'Tanpa Nama',
                    'alamat_asal' => $request->alamat_asal ?: '',
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'status_identitas' => $request->status_identitas,
                    'no_ktp' => $noKtp,
                    'kategori_masalah' => $kategoriStr,
                    'no_rekam_medis' => $request->no_rekam_medis ?: 'Nihil',
                    'keterangan_penanganan' => $request->keterangan_penanganan ?: '',
                ]);

                if ($request->id_tiket && $request->selesaikan_aduan) {
                    Pengaduan::where('id_tiket', $request->id_tiket)
                        ->update(['status_laporan' => 'Selesai']);
                }

                return $record;
            });

            return response()->json([
                'success' => true,
                'message' => 'Data penertiban berhasil disimpan.',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan server saat menyimpan data penertiban.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified Trantibum enforcement log.
     */
    public function update(Request $request, $id = null)
    {
        $recordId = $id ?: $request->id;

        if (!$recordId) {
            return response()->json([
                'error' => 'ID data wajib disertakan untuk melakukan update.'
            ], 400);
        }

        $record = PenertibanTrantibum::find($recordId);
        if (!$record) {
            return response()->json([
                'error' => 'Data penertiban tidak ditemukan.'
            ], 404);
        }

        $request->validate([
            'id_tiket' => 'nullable|string',
            'tanggal_ditemukan' => 'nullable|date',
            'lokasi_ditemukan' => 'required|string',
            'nama_pelaku' => 'required|string',
            'alamat_asal' => 'nullable|string',
            'jenis_kelamin' => 'required|string',
            'status_identitas' => 'required|string',
            'no_ktp' => 'nullable|string',
            'kategori_masalah' => 'required',
            'no_rekam_medis' => 'nullable|string',
            'keterangan_penanganan' => 'nullable|string',
        ]);

        $kategoriStr = is_array($request->kategori_masalah)
            ? implode(', ', $request->kategori_masalah)
            : $request->kategori_masalah;

        $noKtp = $request->status_identitas === 'Tidak Ada' 
            ? '-' 
            : ($request->no_ktp ?: '-');

        $record->update([
            'id_tiket' => $request->id_tiket ?: null,
            'tanggal_ditemukan' => $request->tanggal_ditemukan ? new \DateTime($request->tanggal_ditemukan) : $record->tanggal_ditemukan,
            'lokasi_ditemukan' => $request->lokasi_ditemukan,
            'nama_pelaku' => $request->nama_pelaku ?: 'Tanpa Nama',
            'alamat_asal' => $request->alamat_asal ?: '',
            'jenis_kelamin' => $request->jenis_kelamin,
            'status_identitas' => $request->status_identitas,
            'no_ktp' => $noKtp,
            'kategori_masalah' => $kategoriStr,
            'no_rekam_medis' => $request->no_rekam_medis ?: 'Nihil',
            'keterangan_penanganan' => $request->keterangan_penanganan ?: '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data penertiban berhasil diperbarui.',
            'data' => $record
        ], 200);
    }

    /**
     * Remove the specified Trantibum enforcement log.
     */
    public function destroy(Request $request, $id = null)
    {
        $recordId = $id ?: $request->query('id');

        if (!$recordId) {
            return response()->json([
                'error' => 'Parameter id wajib disertakan.'
            ], 400);
        }

        $record = PenertibanTrantibum::find($recordId);
        if (!$record) {
            return response()->json([
                'error' => 'Data penertiban tidak ditemukan.'
            ], 404);
        }

        $record->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data penertiban berhasil dihapus.'
        ], 200);
    }
}
