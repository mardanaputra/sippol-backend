<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SdaPustaka;
use Illuminate\Http\Request;

class SdaPustakaController extends Controller
{
    /**
     * Display a listing of SDA pustaka/documents.
     */
    public function index()
    {
        $records = SdaPustaka::orderBy('waktu_upload', 'desc')->get();
        // Return with 'waktu_upload' compatible
        $mapped = $records->map(function ($rec) {
            $rec->waktu_upload = $rec->waktu_upload ?: $rec->created_at;
            return $rec;
        });
        return response()->json($mapped, 200);
    }

    /**
     * Store a newly created SDA document/pustaka record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul_dokumen' => 'required|string',
            'jenis_aturan' => 'required|string',
            'nomor_tahun_aturan' => 'required|string',
            'instansi_penerbit' => 'required|string',
            'status_dokumen' => 'required|string',
            'ringkasan_aturan' => 'required|string',
            'tags' => 'nullable|string',
            'berkas_pdf' => 'nullable|string', // Base64 PDF
            'pengunggah' => 'required|string',
        ]);

        $currentYear = now()->format('Y');
        $prefix = "PSTK-SDA-{$currentYear}-";

        // Auto-generate no_arsip
        $latest = SdaPustaka::where('no_arsip', 'like', $prefix . '%')
            ->orderBy('no_arsip', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latest) {
            $parts = explode('-', $latest->no_arsip);
            $lastNum = intval(end($parts));
            if ($lastNum > 0) {
                $nextNumber = $lastNum + 1;
            }
        }
        $no_arsip = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $document = SdaPustaka::create([
            'no_arsip' => $no_arsip,
            'judul_dokumen' => $request->judul_dokumen,
            'jenis_aturan' => $request->jenis_aturan,
            'nomor_tahun_aturan' => $request->nomor_tahun_aturan,
            'instansi_penerbit' => $request->instansi_penerbit,
            'status_dokumen' => $request->status_dokumen,
            'ringkasan_aturan' => $request->ringkasan_aturan,
            'tags' => $request->tags,
            'berkas_pdf' => $request->berkas_pdf,
            'pengunggah' => $request->pengunggah,
            'waktu_upload' => now(),
        ]);

        $document->waktu_upload = $document->waktu_upload ?: $document->created_at;

        return response()->json([
            'success' => true,
            'message' => 'Dokumen pustaka hukum berhasil diunggah.',
            'data' => $document
        ], 201);
    }

    /**
     * Update the specified SDA document record.
     */
    public function update(Request $request, $id = null)
    {
        $docId = $id ?: $request->id;

        if (!$docId) {
            return response()->json([
                'error' => 'ID data wajib disertakan untuk melakukan update.'
            ], 400);
        }

        $document = SdaPustaka::find($docId);
        if (!$document) {
            return response()->json([
                'error' => 'Dokumen tidak ditemukan.'
            ], 404);
        }

        $request->validate([
            'judul_dokumen' => 'required|string',
            'jenis_aturan' => 'required|string',
            'nomor_tahun_aturan' => 'required|string',
            'instansi_penerbit' => 'required|string',
            'status_dokumen' => 'required|string',
            'ringkasan_aturan' => 'required|string',
            'tags' => 'nullable|string',
            'berkas_pdf' => 'nullable|string',
            'pengunggah' => 'required|string',
        ]);

        $document->update([
            'judul_dokumen' => $request->judul_dokumen,
            'jenis_aturan' => $request->jenis_aturan,
            'nomor_tahun_aturan' => $request->nomor_tahun_aturan,
            'instansi_penerbit' => $request->instansi_penerbit,
            'status_dokumen' => $request->status_dokumen,
            'ringkasan_aturan' => $request->ringkasan_aturan,
            'tags' => $request->tags,
            'berkas_pdf' => $request->berkas_pdf !== null ? $request->berkas_pdf : $document->berkas_pdf,
            'pengunggah' => $request->pengunggah,
        ]);

        $document->waktu_upload = $document->waktu_upload ?: $document->created_at;

        return response()->json([
            'success' => true,
            'message' => 'Dokumen pustaka hukum berhasil diperbarui.',
            'data' => $document
        ], 200);
    }

    /**
     * Remove the specified SDA document record.
     */
    public function destroy(Request $request, $id = null)
    {
        $docId = $id ?: $request->query('id');

        if (!$docId) {
            return response()->json([
                'error' => 'Parameter id wajib disertakan.'
            ], 400);
        }

        $document = SdaPustaka::find($docId);
        if (!$document) {
            return response()->json([
                'error' => 'Dokumen tidak ditemukan.'
            ], 404);
        }

        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dokumen pustaka hukum berhasil dihapus.'
        ], 200);
    }
}
