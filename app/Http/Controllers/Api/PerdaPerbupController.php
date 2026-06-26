<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PerdaPerbup;
use Illuminate\Http\Request;

class PerdaPerbupController extends Controller
{
    /**
     * Display a listing of regulations.
     */
    public function index()
    {
        try {
            $regulations = PerdaPerbup::orderBy('created_at', 'desc')->get();
            // Return with 'createdAt' compatibility
            $mapped = $regulations->map(function ($reg) {
                $reg->createdAt = $reg->created_at;
                return $reg;
            });
            return response()->json($mapped, 200);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("PerdaPerbupController@index database query failed: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Database query failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created regulation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'jenis_peraturan' => 'required|string',
            'nomor_peraturan' => 'required|string',
            'tahun_peraturan' => 'required|integer',
            'judul_tentang' => 'required|string',
            'berkas_pdf' => 'nullable|string',
        ]);

        $year = intval($request->tahun_peraturan);
        $typeShort = $request->jenis_peraturan === 'Perda' ? 'PERDA' : 'PERBUP';
        $prefix = "REG-{$typeShort}-{$year}-";

        // Auto-generate kode_regulasi
        $latest = PerdaPerbup::where('kode_regulasi', 'like', $prefix . '%')
            ->orderBy('kode_regulasi', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latest) {
            $parts = explode('-', $latest->kode_regulasi);
            $lastNum = intval(end($parts));
            if ($lastNum > 0) {
                $nextNumber = $lastNum + 1;
            }
        }
        $kode_regulasi = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $regulation = PerdaPerbup::create([
            'kode_regulasi' => $kode_regulasi,
            'jenis_peraturan' => $request->jenis_peraturan,
            'nomor_peraturan' => $request->nomor_peraturan,
            'tahun_peraturan' => $year,
            'judul_tentang' => $request->judul_tentang,
            'berkas_pdf' => $request->berkas_pdf,
        ]);

        $regulation->createdAt = $regulation->created_at;

        return response()->json([
            'success' => true,
            'message' => 'Regulasi berhasil disimpan.',
            'data' => $regulation
        ], 201);
    }

    /**
     * Update the specified regulation.
     */
    public function update(Request $request, $id = null)
    {
        $regId = $id ?: $request->id;

        if (!$regId) {
            return response()->json([
                'error' => 'ID data wajib disertakan untuk melakukan update.'
            ], 400);
        }

        $regulation = PerdaPerbup::find($regId);
        if (!$regulation) {
            return response()->json([
                'error' => 'Data regulasi tidak ditemukan.'
            ], 404);
        }

        $request->validate([
            'jenis_peraturan' => 'required|string',
            'nomor_peraturan' => 'required|string',
            'tahun_peraturan' => 'required|integer',
            'judul_tentang' => 'required|string',
            'berkas_pdf' => 'nullable|string',
        ]);

        $regulation->update([
            'jenis_peraturan' => $request->jenis_peraturan,
            'nomor_peraturan' => $request->nomor_peraturan,
            'tahun_peraturan' => intval($request->tahun_peraturan),
            'judul_tentang' => $request->judul_tentang,
            'berkas_pdf' => $request->berkas_pdf !== null ? $request->berkas_pdf : $regulation->berkas_pdf,
        ]);

        $regulation->createdAt = $regulation->created_at;

        return response()->json([
            'success' => true,
            'message' => 'Regulasi berhasil diperbarui.',
            'data' => $regulation
        ], 200);
    }

    /**
     * Remove the specified regulation.
     */
    public function destroy(Request $request, $id = null)
    {
        $regId = $id ?: $request->query('id');

        if (!$regId) {
            return response()->json([
                'error' => 'Parameter id wajib disertakan.'
            ], 400);
        }

        $regulation = PerdaPerbup::find($regId);
        if (!$regulation) {
            return response()->json([
                'error' => 'Data regulasi tidak ditemukan.'
            ], 404);
        }

        $regulation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Regulasi berhasil dihapus.'
        ], 200);
    }
}
