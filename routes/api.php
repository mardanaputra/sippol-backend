<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PengaduanController;
use App\Http\Controllers\Api\DisposisiController;

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
    Route::delete('/pengaduan/{id}', [PengaduanController::class, 'destroy']);

    // Admin Disposition Management
    Route::get('/disposisi', [DisposisiController::class, 'index']);
    Route::post('/disposisi', [DisposisiController::class, 'store']);
});
