<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenegakanPerada;
use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenegakanPeradaController extends Controller
{
    /**
     * Display a listing of regulation enforcement logs.
     */
    public function index(Request $request)
    {
        $type = $request->query('type');

        if ($type === 'delegated') {
            $delegatedReports = Pengaduan::with('disposisi')
                ->where('status_laporan', 'Disposisi')
                ->where('bidang_disposisi', 'Bidang Perada')
                ->orderBy('waktu_kirim', 'desc')
                ->get();

            return response()->json($delegatedReports, 200);
        }

        $records = PenegakanPerada::orderBy('tanggal_tindakan', 'desc')->get();
        
        $mapped = $records->map(function ($rec) {
            $rec->createdAt = $rec->created_at;
            return $rec;
        });

        return response()->json($mapped, 200);
    }

    /**
     * Store a newly created regulation enforcement log.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_tiket' => 'nullable|string',
            'tanggal_tindakan' => 'nullable|date',
            'nama_pelanggar' => 'required|string',
            'nik_pelanggar' => 'nullable|string',
            'alamat_pelanggar' => 'nullable|string',
            'lokasi_kejadian' => 'required|string',
            'kode_regulasi' => 'required|string',
            'pasal_dilanggar' => 'required|string',
            'jenis_tindakan' => 'required|string', // Yustisial, Tipiring
            'status_sidang' => 'nullable|string',
            'tanggal_sidang' => 'nullable|date',
            'lokasi_sidang' => 'nullable|string',
            'denda_dijatuhkan' => 'nullable|numeric',
            'no_bukti_setor' => 'nullable|string',
            'scan_dokumen' => 'nullable|string', // Base64 BAP/document
            'bukti_setor_kas' => 'nullable|string', // Base64 payment slip
            'kronologi_singkat' => 'nullable|string',
            'barang_bukti' => 'nullable|string',
            'catatan' => 'required|string',
            'selesaikan_aduan' => 'nullable|boolean',
        ]);

        $dateObj = $request->tanggal_tindakan ? new \DateTime($request->tanggal_tindakan) : now();
        $currentYear = $dateObj->format('Y');
        $prefix = "BAP/PERADA/{$currentYear}/";

        // Auto-generate no_kejadian BAP code
        $latest = PenegakanPerada::where('no_kejadian', 'like', $prefix . '%')
            ->orderBy('no_kejadian', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latest) {
            $parts = explode('/', $latest->no_kejadian);
            $lastNum = intval(end($parts));
            if ($lastNum > 0) {
                $nextNumber = $lastNum + 1;
            }
        }
        $no_kejadian = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        try {
            $result = DB::transaction(function () use ($request, $no_kejadian, $dateObj) {
                $record = PenegakanPerada::create([
                    'no_kejadian' => $no_kejadian,
                    'id_tiket' => $request->id_tiket ?: null,
                    'tanggal_tindakan' => $dateObj,
                    'nama_pelanggar' => $request->nama_pelanggar,
                    'nik_pelanggar' => $request->nik_pelanggar ?: null,
                    'alamat_pelanggar' => $request->alamat_pelanggar ?: null,
                    'lokasi_kejadian' => $request->lokasi_kejadian,
                    'kode_regulasi' => $request->kode_regulasi,
                    'pasal_dilanggar' => $request->pasal_dilanggar,
                    'jenis_tindakan' => $request->jenis_tindakan,
                    'status_sidang' => $request->status_sidang ?: 'Penyelidikan / Pemanggilan',
                    'tanggal_sidang' => $request->tanggal_sidang ? new \DateTime($request->tanggal_sidang) : null,
                    'lokasi_sidang' => $request->lokasi_sidang ?: null,
                    'denda_dijatuhkan' => $request->denda_dijatuhkan !== null ? doubleval($request->denda_dijatuhkan) : null,
                    'no_bukti_setor' => $request->no_bukti_setor ?: null,
                    'scan_dokumen' => $request->scan_dokumen ?: null,
                    'bukti_setor_kas' => $request->bukti_setor_kas ?: null,
                    'kronologi_singkat' => $request->kronologi_singkat ?: null,
                    'barang_bukti' => $request->barang_bukti ?: null,
                    'catatan' => $request->catatan,
                ]);

                if ($request->id_tiket && $request->selesaikan_aduan) {
                    Pengaduan::where('id_tiket', $request->id_tiket)
                        ->update(['status_laporan' => 'Selesai']);
                }

                return $record;
            });

            $result->createdAt = $result->created_at;

            return response()->json([
                'success' => true,
                'message' => 'Laporan penindakan berhasil disimpan.',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan server saat menyimpan laporan penindakan.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified regulation enforcement log.
     */
    public function update(Request $request, $id = null)
    {
        $recordId = $id ?: $request->id;

        if (!$recordId) {
            return response()->json([
                'error' => 'ID data wajib disertakan untuk melakukan update.'
            ], 400);
        }

        $record = PenegakanPerada::find($recordId);
        if (!$record) {
            return response()->json([
                'error' => 'Laporan penindakan tidak ditemukan.'
            ], 404);
        }

        $request->validate([
            'id_tiket' => 'nullable|string',
            'tanggal_tindakan' => 'nullable|date',
            'nama_pelanggar' => 'required|string',
            'nik_pelanggar' => 'nullable|string',
            'alamat_pelanggar' => 'nullable|string',
            'lokasi_kejadian' => 'required|string',
            'kode_regulasi' => 'required|string',
            'pasal_dilanggar' => 'required|string',
            'jenis_tindakan' => 'required|string',
            'status_sidang' => 'nullable|string',
            'tanggal_sidang' => 'nullable|date',
            'lokasi_sidang' => 'nullable|string',
            'denda_dijatuhkan' => 'nullable|numeric',
            'no_bukti_setor' => 'nullable|string',
            'scan_dokumen' => 'nullable|string',
            'bukti_setor_kas' => 'nullable|string',
            'kronologi_singkat' => 'nullable|string',
            'barang_bukti' => 'nullable|string',
            'catatan' => 'required|string',
        ]);

        $record->update([
            'id_tiket' => $request->id_tiket ?: null,
            'tanggal_tindakan' => $request->tanggal_tindakan ? new \DateTime($request->tanggal_tindakan) : $record->tanggal_tindakan,
            'nama_pelanggar' => $request->nama_pelanggar,
            'nik_pelanggar' => $request->nik_pelanggar ?: null,
            'alamat_pelanggar' => $request->alamat_pelanggar ?: null,
            'lokasi_kejadian' => $request->lokasi_kejadian,
            'kode_regulasi' => $request->kode_regulasi,
            'pasal_dilanggar' => $request->pasal_dilanggar,
            'jenis_tindakan' => $request->jenis_tindakan,
            'status_sidang' => $request->status_sidang ?: 'Penyelidikan / Pemanggilan',
            'tanggal_sidang' => $request->tanggal_sidang ? new \DateTime($request->tanggal_sidang) : null,
            'lokasi_sidang' => $request->lokasi_sidang ?: null,
            'denda_dijatuhkan' => $request->denda_dijatuhkan !== null ? doubleval($request->denda_dijatuhkan) : null,
            'no_bukti_setor' => $request->no_bukti_setor ?: null,
            'scan_dokumen' => $request->scan_dokumen !== null ? $request->scan_dokumen : $record->scan_dokumen,
            'bukti_setor_kas' => $request->bukti_setor_kas !== null ? $request->bukti_setor_kas : $record->bukti_setor_kas,
            'kronologi_singkat' => $request->kronologi_singkat ?: null,
            'barang_bukti' => $request->barang_bukti ?: null,
            'catatan' => $request->catatan,
        ]);

        $record->createdAt = $record->created_at;

        return response()->json([
            'success' => true,
            'message' => 'Laporan penindakan berhasil diperbarui.',
            'data' => $record
        ], 200);
    }

    /**
     * Remove the specified regulation enforcement log.
     */
    public function destroy(Request $request, $id = null)
    {
        $recordId = $id ?: $request->query('id');

        if (!$recordId) {
            return response()->json([
                'error' => 'Parameter id wajib disertakan.'
            ], 400);
        }

        $record = PenegakanPerada::find($recordId);
        if (!$record) {
            return response()->json([
                'error' => 'Laporan penindakan tidak ditemukan.'
            ], 404);
        }

        $record->delete();

        return response()->json([
            'success' => true,
            'message' => 'Laporan penindakan berhasil dihapus.'
        ], 200);
    }
}
