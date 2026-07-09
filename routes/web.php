<?php

use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\OperatorIspController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\TiangController;
use Illuminate\Support\Facades\Route;

// ============================================================
// HEALTH CHECK (tanpa auth, rate limited)
// ============================================================
Route::get('/health', [HealthController::class, 'check'])->middleware('throttle:30,1')->name('health');

// ============================================================
// AUTH
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    // [KEAMANAN] Rate limiter login — 5 percobaan/menit per email+IP (didefinisikan di AppServiceProvider)
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/', function () {
    if (auth()->check()) return redirect()->route('dashboard');

    $totalTiang = \App\Models\TiangTelekomunikasi::whereNull('deleted_at')->count();
    $totalSto = \App\Models\Sto::whereNull('deleted_at')->count();
    
    // Verifikasi rate (%)
    $totalVerified = \App\Models\TiangTelekomunikasi::whereNull('deleted_at')->where('status_verifikasi', 'ok')->count();
    $verifikasiRate = $totalTiang > 0 ? round(($totalVerified / $totalTiang) * 100, 1) : 0.0;
    
    // Anomali Terselesaikan
    $anomaliSelesai = \App\Models\AnomalyLog::where('status', 'resolved')->count();

    return view('landing', compact('totalTiang', 'totalSto', 'verifikasiRate', 'anomaliSelesai'));
})->name('landing');

Route::get('/public-map-markers', function () {
    $markers = \App\Models\TiangTelekomunikasi::whereNull('deleted_at')
        ->select('id', 'kode_tiang', 'latitude', 'longitude', 'status_verifikasi', 'has_anomali')
        ->limit(1000)
        ->get();
    return response()->json($markers);
});

// ============================================================
// AUTHENTICATED ROUTES
// ============================================================
Route::middleware(['auth', 'session.timeout'])->group(function () {

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

    // === TINDAK LANJUT ISP PENUMPANG ===
    Route::get('/tindaklanjut', [\App\Http\Controllers\TindakLanjutController::class, 'index'])
         ->name('tindaklanjut.index');
    Route::get('/tindaklanjut/data', [\App\Http\Controllers\TindakLanjutController::class, 'data'])
         ->name('tindaklanjut.data');
    Route::get('/tindaklanjut/{tiangOperator}', [\App\Http\Controllers\TindakLanjutController::class, 'show'])
         ->name('tindaklanjut.show');
    Route::patch('/tindaklanjut/{tiangOperator}/selesai', [\App\Http\Controllers\TindakLanjutController::class, 'selesai'])
         ->name('tindaklanjut.selesai')->middleware('role:admin');
    Route::patch('/tindaklanjut/{tiangOperator}/reset', [\App\Http\Controllers\TindakLanjutController::class, 'reset'])
         ->name('tindaklanjut.reset')->middleware('role:admin');
    Route::get('/tindaklanjut/{tiangOperator}/timeline', [\App\Http\Controllers\TindakLanjutController::class, 'timelinePartial'])
         ->name('tindaklanjut.timeline');

    // Surat
    Route::post('/isp-surat', [\App\Http\Controllers\IspSuratController::class, 'store'])
         ->name('isp-surat.store');
    Route::delete('/isp-surat/{ispSurat}', [\App\Http\Controllers\IspSuratController::class, 'destroy'])
         ->name('isp-surat.destroy')->middleware('role:admin');

    // Balasan
    Route::post('/isp-balasan', [\App\Http\Controllers\IspBalasanController::class, 'store'])
         ->name('isp-balasan.store');
    Route::delete('/isp-balasan/{ispBalasan}', [\App\Http\Controllers\IspBalasanController::class, 'destroy'])
         ->name('isp-balasan.destroy')->middleware('role:admin');

    // Follow-up
    Route::post('/isp-followup', [\App\Http\Controllers\IspFollowupController::class, 'store'])
         ->name('isp-followup.store');
    Route::delete('/isp-followup/{ispFollowup}', [\App\Http\Controllers\IspFollowupController::class, 'destroy'])
         ->name('isp-followup.destroy')->middleware('role:admin');
});
