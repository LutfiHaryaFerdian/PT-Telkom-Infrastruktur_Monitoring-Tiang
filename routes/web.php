<?php

use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\OperatorIspController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\TiangController;
use Illuminate\Support\Facades\Route;

// ============================================================
// AUTH
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ============================================================
// REDIRECT ROOT
// ============================================================
Route::get('/', fn () => redirect()->route('dashboard'));

// ============================================================
// AUTHENTICATED ROUTES
// ============================================================
Route::middleware('auth')->group(function () {

    // === DASHBOARD ===
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // === TIANG ===
    Route::get('/tiang', [TiangController::class, 'index'])->name('tiang.index');
    Route::get('/tiang/create', [TiangController::class, 'create'])->name('tiang.create');
    Route::post('/tiang', [TiangController::class, 'store'])->name('tiang.store');
    Route::get('/tiang/trashed', [TiangController::class, 'trashed'])->name('tiang.trashed');
    Route::get('/tiang/data', [TiangController::class, 'data'])->name('tiang.data'); // AJAX DataTables
    Route::get('/tiang/{tiang}', [TiangController::class, 'show'])->name('tiang.show');
    Route::get('/tiang/{tiang}/edit', [TiangController::class, 'edit'])->name('tiang.edit');
    Route::put('/tiang/{tiang}', [TiangController::class, 'update'])->name('tiang.update');
    Route::delete('/tiang/{tiang}', [TiangController::class, 'destroy'])->name('tiang.destroy');
    Route::patch('/tiang/{tiang}/restore', [TiangController::class, 'restore'])->name('tiang.restore');

    // === INSPEKSI ===
    Route::post('/tiang/{tiang}/inspeksi', [InspectionController::class, 'store'])->name('inspection.store');
    Route::get('/inspeksi/{inspection}', [InspectionController::class, 'show'])->name('inspection.show');
    Route::post('/inspeksi/{inspection}/apply-koordinat', [InspectionController::class, 'applyKoordinat'])->name('inspection.apply-koordinat');
    Route::delete('/inspeksi/{inspection}', [InspectionController::class, 'destroy'])->name('inspection.destroy');

    // === EXPORT ===
    Route::get('/export', [ExportController::class, 'index'])->name('export.index');
    Route::post('/export', [ExportController::class, 'export'])->name('export.store');

    // ============================================================
    // ADMIN ONLY
    // ============================================================
    Route::middleware('role:admin')->group(function () {

        // === IMPORT ===
        Route::get('/import', [ImportController::class, 'index'])->name('import.index');
        Route::post('/import', [ImportController::class, 'store'])->name('import.store');
        Route::get('/import/{history}', [ImportController::class, 'show'])->name('import.show');

        // === MASTER DATA ===
        Route::prefix('master')->name('master.')->group(function () {

            // District
            Route::resource('districts', MasterDataController::class)
                ->parameters(['districts' => 'district'])
                ->only(['index', 'store', 'update', 'destroy'])
                ->names([
                    'index'   => 'districts.index',
                    'store'   => 'districts.store',
                    'update'  => 'districts.update',
                    'destroy' => 'districts.destroy',
                ]);

            // Area
            Route::get('areas', [MasterDataController::class, 'areasIndex'])->name('areas.index');
            Route::post('areas', [MasterDataController::class, 'areasStore'])->name('areas.store');
            Route::put('areas/{area}', [MasterDataController::class, 'areasUpdate'])->name('areas.update');
            Route::delete('areas/{area}', [MasterDataController::class, 'areasDestroy'])->name('areas.destroy');

            // STO
            Route::get('stos', [MasterDataController::class, 'stosIndex'])->name('stos.index');
            Route::post('stos', [MasterDataController::class, 'stosStore'])->name('stos.store');
            Route::put('stos/{sto}', [MasterDataController::class, 'stosUpdate'])->name('stos.update');
            Route::delete('stos/{sto}', [MasterDataController::class, 'stosDestroy'])->name('stos.destroy');
            Route::patch('stos/{sto}/restore', [MasterDataController::class, 'stosRestore'])->name('stos.restore');
            Route::get('stos/trashed', [MasterDataController::class, 'stosTrashed'])->name('stos.trashed');

            // Jenis Tiang
            Route::get('jenis-tiang', [MasterDataController::class, 'jenisTiangIndex'])->name('jenis-tiang.index');
            Route::post('jenis-tiang', [MasterDataController::class, 'jenisTiangStore'])->name('jenis-tiang.store');
            Route::put('jenis-tiang/{jenisTiang}', [MasterDataController::class, 'jenisTiangUpdate'])->name('jenis-tiang.update');
            Route::delete('jenis-tiang/{jenisTiang}', [MasterDataController::class, 'jenisTiangDestroy'])->name('jenis-tiang.destroy');

            // Kondisi Tiang
            Route::get('kondisi-tiang', [MasterDataController::class, 'kondisiTiangIndex'])->name('kondisi-tiang.index');
            Route::post('kondisi-tiang', [MasterDataController::class, 'kondisiTiangStore'])->name('kondisi-tiang.store');
            Route::put('kondisi-tiang/{kondisiTiang}', [MasterDataController::class, 'kondisiTiangUpdate'])->name('kondisi-tiang.update');
            Route::delete('kondisi-tiang/{kondisiTiang}', [MasterDataController::class, 'kondisiTiangDestroy'])->name('kondisi-tiang.destroy');

            // Operator ISP
            Route::get('operator-isp', [OperatorIspController::class, 'index'])->name('operator-isp.index');
            Route::post('operator-isp', [OperatorIspController::class, 'store'])->name('operator-isp.store');
            Route::put('operator-isp/{operatorIsp}', [OperatorIspController::class, 'update'])->name('operator-isp.update');
            Route::delete('operator-isp/{operatorIsp}', [OperatorIspController::class, 'destroy'])->name('operator-isp.destroy');
            Route::patch('operator-isp/{operatorIsp}/restore', [OperatorIspController::class, 'restore'])->name('operator-isp.restore');
            Route::get('operator-isp/trashed', [OperatorIspController::class, 'trashed'])->name('operator-isp.trashed');
        });
    });
});
