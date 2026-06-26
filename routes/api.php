<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PengaduanController;
use App\Http\Controllers\Api\DisposisiController;
use App\Http\Controllers\Api\ReguPatroliController;
use App\Http\Controllers\Api\PenertibanK3Controller;
use App\Http\Controllers\Api\PenertibanTrantibumController;
use App\Http\Controllers\Api\SatlinmasController;
use App\Http\Controllers\Api\KegiatanLinmasController;
use App\Http\Controllers\Api\PerdaPerbupController;
use App\Http\Controllers\Api\KatalogPelanggaranController;
use App\Http\Controllers\Api\PenegakanPeradaController;
use App\Http\Controllers\Api\SdaPersonelController;
use App\Http\Controllers\Api\SdaKegiatanController;
use App\Http\Controllers\Api\SdaPustakaController;
use App\Http\Controllers\Api\SatpolKegiatanController;

// Auth Routes (Public)
Route::post('/login', [AuthController::class, 'login']);

// Complaint Public Routes (Warga)
Route::post('/pengaduan', [PengaduanController::class, 'store']);
Route::get('/pengaduan/{id}', [PengaduanController::class, 'show']);

// Authenticated Admin Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth Status & Session
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Admin Complaint Management
    Route::get('/pengaduan', [PengaduanController::class, 'index']);
    Route::delete('/pengaduan/{id?}', [PengaduanController::class, 'destroy']);

    // Admin Disposition Management
    Route::get('/disposisi', [DisposisiController::class, 'index']);
    Route::post('/disposisi', [DisposisiController::class, 'store']);

    // Modul Trantibum (Ketertiban Umum)
    Route::get('/trantib/patroli', [ReguPatroliController::class, 'index']);
    Route::post('/trantib/patroli', [ReguPatroliController::class, 'store']);
    Route::put('/trantib/patroli/{id?}', [ReguPatroliController::class, 'update']);
    Route::delete('/trantib/patroli/{id?}', [ReguPatroliController::class, 'destroy']);

    Route::get('/trantib/penertiban', [PenertibanK3Controller::class, 'index']);
    Route::post('/trantib/penertiban', [PenertibanK3Controller::class, 'store']);
    Route::put('/trantib/penertiban/{id?}', [PenertibanK3Controller::class, 'update']);
    Route::delete('/trantib/penertiban/{id?}', [PenertibanK3Controller::class, 'destroy']);

    // Modul Linmas (Perlindungan Masyarakat)
    Route::get('/linmas/satlinmas', [SatlinmasController::class, 'index']);
    Route::post('/linmas/satlinmas', [SatlinmasController::class, 'store']);
    Route::put('/linmas/satlinmas/{id?}', [SatlinmasController::class, 'update']);
    Route::delete('/linmas/satlinmas/{id?}', [SatlinmasController::class, 'destroy']);

    Route::get('/linmas/penertiban', [PenertibanTrantibumController::class, 'index']);
    Route::post('/linmas/penertiban', [PenertibanTrantibumController::class, 'store']);
    Route::put('/linmas/penertiban/{id?}', [PenertibanTrantibumController::class, 'update']);
    Route::delete('/linmas/penertiban/{id?}', [PenertibanTrantibumController::class, 'destroy']);

    Route::get('/linmas/kegiatan', [KegiatanLinmasController::class, 'index']);
    Route::post('/linmas/kegiatan', [KegiatanLinmasController::class, 'store']);
    Route::put('/linmas/kegiatan/{id?}', [KegiatanLinmasController::class, 'update']);
    Route::delete('/linmas/kegiatan/{id?}', [KegiatanLinmasController::class, 'destroy']);

    // Modul Perada (Penegakan Perda/Perbup)
    Route::get('/perada/regulasi', [PerdaPerbupController::class, 'index']);
    Route::post('/perada/regulasi', [PerdaPerbupController::class, 'store']);
    Route::put('/perada/regulasi/{id?}', [PerdaPerbupController::class, 'update']);
    Route::delete('/perada/regulasi/{id?}', [PerdaPerbupController::class, 'destroy']);

    Route::get('/perada/pelanggaran', [KatalogPelanggaranController::class, 'index']);
    Route::post('/perada/pelanggaran', [KatalogPelanggaranController::class, 'store']);
    Route::delete('/perada/pelanggaran/{id?}', [KatalogPelanggaranController::class, 'destroy']);

    Route::get('/perada/penegakan', [PenegakanPeradaController::class, 'index']);
    Route::post('/perada/penegakan', [PenegakanPeradaController::class, 'store']);
    Route::put('/perada/penegakan/{id?}', [PenegakanPeradaController::class, 'update']);
    Route::delete('/perada/penegakan/{id?}', [PenegakanPeradaController::class, 'destroy']);

    // Modul SDA (Sumber Daya Aparatur)
    Route::get('/sda/personel', [SdaPersonelController::class, 'index']);
    Route::post('/sda/personel', [SdaPersonelController::class, 'store']);
    Route::put('/sda/personel/{id?}', [SdaPersonelController::class, 'update']);
    Route::delete('/sda/personel/{id?}', [SdaPersonelController::class, 'destroy']);

    Route::get('/sda/kegiatan', [SdaKegiatanController::class, 'index']);
    Route::post('/sda/kegiatan', [SdaKegiatanController::class, 'store']);
    Route::put('/sda/kegiatan/{id?}', [SdaKegiatanController::class, 'update']);
    Route::delete('/sda/kegiatan/{id?}', [SdaKegiatanController::class, 'destroy']);

    Route::get('/sda/pustaka', [SdaPustakaController::class, 'index']);
    Route::post('/sda/pustaka', [SdaPustakaController::class, 'store']);
    Route::put('/sda/pustaka/{id?}', [SdaPustakaController::class, 'update']);
    Route::delete('/sda/pustaka/{id?}', [SdaPustakaController::class, 'destroy']);

    // Modul Kegiatan Satpol PP (Portal Kegiatan)
    Route::get('/admin/kegiatan', [SatpolKegiatanController::class, 'index']);
    Route::post('/admin/kegiatan', [SatpolKegiatanController::class, 'store']);
    Route::put('/admin/kegiatan/{id?}', [SatpolKegiatanController::class, 'update']);
    Route::delete('/admin/kegiatan/{id?}', [SatpolKegiatanController::class, 'destroy']);
});
