<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnomalyLog;
use App\Models\District;
use App\Models\OperatorIsp;
use App\Models\Sto;
use App\Models\TiangTelekomunikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    protected function success($data, string $message = 'OK'): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data, 'message' => $message]);
    }

    /**
     * GET /api/dashboard/stats
     * Param filter: district_id, area_id, sto_id, date_from, date_to (semua opsional).
     * Dashboard session persist ke session untuk UI,
     * tapi API selalu terima parameter — tidak bergantung session.
     */
    public function stats(Request $request): JsonResponse
    {
        // Base query dengan filter
        $baseQuery = TiangTelekomunikasi::query()->whereNull('deleted_at');

        if ($request->filled('district_id')) {
            $baseQuery->whereHas('sto.area', fn($q) => $q->where('district_id', $request->integer('district_id')));
        }
        if ($request->filled('area_id')) {
            $baseQuery->whereHas('sto', fn($q) => $q->where('area_id', $request->integer('area_id')));
        }
        if ($request->filled('sto_id')) {
            $baseQuery->where('sto_id', $request->integer('sto_id'));
        }
        if ($request->filled('date_from')) {
            $baseQuery->whereDate('tgl_input', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $baseQuery->whereDate('tgl_input', '<=', $request->get('date_to'));
        }

        $totalTiang = (clone $baseQuery)->count();

        // Tiang dengan kondisi OK (level = baik)
        $tiangKondisiOk = (clone $baseQuery)
            ->whereHas('kondisiTiang', fn($q) => $q->where('level', 'baik'))
            ->count();

        // Tiang dengan kondisi NOK
        $tiangKondisiNok = (clone $baseQuery)
            ->whereHas('kondisiTiang', fn($q) => $q->whereIn('level', ['perlu_perhatian', 'rusak']))
            ->count();

        // Anomali aktif
        $tiangIds = (clone $baseQuery)->pluck('id');
        $anomaliAktif = AnomalyLog::whereIn('tiang_id', $tiangIds)->where('status', 'aktif')->count();

        // Tiang menunggu verifikasi
        $tiangPendingVerifikasi = (clone $baseQuery)->where('status_verifikasi', 'pending')->count();

        // Per STO (top 10)
        $perSto = (clone $baseQuery)
            ->selectRaw('sto_id, COUNT(*) as total')
            ->with('sto:id,kode,nama')
            ->groupBy('sto_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'sto_kode' => $item->sto?->kode,
                'sto_nama' => $item->sto?->nama,
                'total'    => $item->total,
            ]);

        // Per Kondisi
        $perKondisi = (clone $baseQuery)
            ->selectRaw('kondisi_tiang_id, COUNT(*) as total')
            ->with('kondisiTiang:id,nama,level')
            ->groupBy('kondisi_tiang_id')
            ->get()
            ->map(fn ($item) => [
                'kondisi_nama'  => $item->kondisiTiang?->nama,
                'kondisi_level' => $item->kondisiTiang?->level,
                'total'         => $item->total,
            ]);

        // Top 5 Operator (berdasarkan jumlah tiang)
        $perOperatorTop5 = DB::table('tiang_operator')
            ->join('operator_isp', 'operator_isp.id', '=', 'tiang_operator.operator_id')
            ->join('tiang_telekomunikasi', 'tiang_telekomunikasi.id', '=', 'tiang_operator.tiang_id')
            ->whereNull('tiang_telekomunikasi.deleted_at')
            ->whereNull('operator_isp.deleted_at')
            ->when($tiangIds->isNotEmpty(), fn($q) => $q->whereIn('tiang_operator.tiang_id', $tiangIds))
            ->selectRaw('operator_isp.nama_operator, COUNT(*) as total')
            ->groupBy('operator_isp.nama_operator')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Statistik global (tidak dipengaruhi filter tiang)
        $totalDistrict = District::count();
        $totalArea     = DB::table('areas')->count();
        $totalSto      = Sto::whereNull('deleted_at')->count();
        $totalOperator = OperatorIsp::whereNull('deleted_at')->count();

        return $this->success([
            // Global stats
            'total_district'           => $totalDistrict,
            'total_area'               => $totalArea,
            'total_sto'                => $totalSto,
            'total_operator'           => $totalOperator,
            // Filtered stats
            'total_tiang'              => $totalTiang,
            'tiang_kondisi_ok'         => $tiangKondisiOk,
            'tiang_kondisi_nok'        => $tiangKondisiNok,
            'anomali_aktif'            => $anomaliAktif,
            'tiang_pending_verifikasi' => $tiangPendingVerifikasi,
            // Chart data
            'per_sto'                  => $perSto,
            'per_kondisi'              => $perKondisi,
            'per_operator_top5'        => $perOperatorTop5,
        ]);
    }
}
