<?php

use App\Http\Controllers\Api\AnomalyApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ImportApiController;
use App\Http\Controllers\Api\MasterApiController;
use App\Http\Controllers\Api\TiangApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth'])->group(function () {

    // === DASHBOARD (throttle standar 60/menit) ===
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/dashboard/stats', [DashboardApiController::class, 'stats']);
    });

    // === GIS MAP & SEARCH (throttle ketat — query berat) ===
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/tiang/map', [TiangApiController::class, 'map']);
        Route::get('/tiang/heatmap', [TiangApiController::class, 'heatmap']);
        Route::get('/tiang/map/bounds', [TiangApiController::class, 'bounds']);
        Route::get('/tiang/{id}', [TiangApiController::class, 'show'])->whereNumber('id');
        Route::get('/search/tiang', [TiangApiController::class, 'search']);
    });

    // === TIANG WRITE ACTIONS (throttle write 30/menit) ===
    Route::middleware(['role:admin', 'throttle:30,1'])->group(function () {
        Route::patch('/tiang/{id}/verifikasi', [TiangApiController::class, 'verifikasi']);
        Route::patch('/tiang/{id}/kode', [TiangApiController::class, 'updateKode']);
        Route::patch('/tiang/{id}/isp/{operator_id}/legalitas', [TiangApiController::class, 'updateLegalitas']);
    });

    // === FOTO TIANG (throttle write 30/menit) ===
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/tiang/{id}/foto', [TiangApiController::class, 'uploadFoto']);
    });

    // === ANOMALI (throttle standar) ===
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/anomali/aktif', [AnomalyApiController::class, 'aktif']);
    });
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/anomali/{id}/resolve', [AnomalyApiController::class, 'resolve']);
    });

    // === MASTER DATA dropdown AJAX (throttle standar 60/menit) ===
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/master/districts', [MasterApiController::class, 'districts']);
        Route::get('/master/areas', [MasterApiController::class, 'areas']);
        Route::get('/master/stos', [MasterApiController::class, 'stos']);
        Route::get('/master/jenis-tiang', [MasterApiController::class, 'jenisTiang']);
        Route::get('/master/kondisi-tiang', [MasterApiController::class, 'kondisiTiang']);
        Route::get('/master/operator-isp', [MasterApiController::class, 'operatorIsp']);
    });

    // === IMPORT progress check (throttle ketat 5/menit — bisa berat) ===
    Route::middleware('throttle:5,1')->group(function () {
        Route::get('/import/{id}/progress', [ImportApiController::class, 'progress']);
    });

    // === TINDAK LANJUT ISP POPUP GIS ===
    Route::get('/tiang/{tiang}/isp-status', [\App\Http\Controllers\TindakLanjutController::class, 'apiIspStatus'])
         ->name('api.tiang.isp-status');
});
