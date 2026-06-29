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

    // === DASHBOARD ===
    Route::get('/dashboard/stats', [DashboardApiController::class, 'stats']);

    // === GIS MAP ===
    Route::get('/tiang/map', [TiangApiController::class, 'map']);
    Route::get('/tiang/map/bounds', [TiangApiController::class, 'bounds']);
    Route::get('/tiang/{id}', [TiangApiController::class, 'show'])->whereNumber('id');

    // === SEARCH ===
    Route::get('/search/tiang', [TiangApiController::class, 'search']);

    // === TIANG ACTIONS ===
    Route::middleware('role:admin')->group(function () {
        Route::patch('/tiang/{id}/verifikasi', [TiangApiController::class, 'verifikasi']);
        Route::patch('/tiang/{id}/kode', [TiangApiController::class, 'updateKode']);
        Route::patch('/tiang/{id}/isp/{operator_id}/legalitas', [TiangApiController::class, 'updateLegalitas']);
    });

    // === FOTO TIANG ===
    Route::post('/tiang/{id}/foto', [TiangApiController::class, 'uploadFoto']);

    // === ANOMALI ===
    Route::get('/anomali/aktif', [AnomalyApiController::class, 'aktif']);
    Route::post('/anomali/{id}/resolve', [AnomalyApiController::class, 'resolve']);

    // === MASTER DATA (dropdown AJAX) ===
    Route::get('/master/districts', [MasterApiController::class, 'districts']);
    Route::get('/master/areas', [MasterApiController::class, 'areas']);
    Route::get('/master/stos', [MasterApiController::class, 'stos']);
    Route::get('/master/jenis-tiang', [MasterApiController::class, 'jenisTiang']);
    Route::get('/master/kondisi-tiang', [MasterApiController::class, 'kondisiTiang']);
    Route::get('/master/operator-isp', [MasterApiController::class, 'operatorIsp']);

    // === IMPORT ===
    Route::get('/import/{id}/progress', [ImportApiController::class, 'progress']);
});
