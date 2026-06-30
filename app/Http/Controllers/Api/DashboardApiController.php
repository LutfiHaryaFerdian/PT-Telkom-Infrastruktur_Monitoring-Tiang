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
use Illuminate\Support\Facades\Cache;
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
     * [PERFORMA] Di-cache 5 menit per kombinasi filter unik.
     */
    public function stats(Request $request): JsonResponse
    {
        $filters = $request->only(['district_id', 'area_id', 'sto_id', 'date_from', 'date_to']);
        // [PERFORMA] Versi cache diubah oleh TiangObserver saat ada perubahan data tiang.
        // Menyertakan versi di key memastikan data lama tidak digunakan setelah ada update.
        $version  = Cache::get('dashboard_version', 'v1');
        $cacheKey = 'dashboard_stats_' . md5(json_encode($filters) . $version);

        $result = Cache::remember($cacheKey, 300, function () use ($filters) {
            // Base query dengan filter
            $baseQuery = TiangTelekomunikasi::query()->whereNull('deleted_at');

            if (!empty($filters['district_id'])) {
                $baseQuery->whereHas('sto.area', fn($q) => $q->where('district_id', (int)$filters['district_id']));
            }
            if (!empty($filters['area_id'])) {
                $baseQuery->whereHas('sto', fn($q) => $q->where('area_id', (int)$filters['area_id']));
            }
            if (!empty($filters['sto_id'])) {
                $baseQuery->where('sto_id', (int)$filters['sto_id']);
            }
            if (!empty($filters['date_from'])) {
                $baseQuery->whereDate('tgl_input', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $baseQuery->whereDate('tgl_input', '<=', $filters['date_to']);
            }

            $totalTiang = (clone $baseQuery)->count();

            $tiangKondisiOk = (clone $baseQuery)
                ->whereHas('kondisiTiang', fn($q) => $q->where('level', 'baik'))
                ->count();

            $tiangKondisiNok = (clone $baseQuery)
                ->whereHas('kondisiTiang', fn($q) => $q->whereIn('level', ['perlu_perhatian', 'rusak']))
                ->count();

            $tiangIds = (clone $baseQuery)->pluck('id');
            $anomaliAktif = AnomalyLog::whereIn('tiang_id', $tiangIds)->where('status', 'aktif')->count();

            $tiangPendingVerifikasi = (clone $baseQuery)->where('status_verifikasi', 'pending')->count();

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
                    'total'    => (int)$item->total,
                ])
                ->toArray();

            $perKondisi = (clone $baseQuery)
                ->selectRaw('kondisi_tiang_id, COUNT(*) as total')
                ->with('kondisiTiang:id,nama,level')
                ->groupBy('kondisi_tiang_id')
                ->get()
                ->map(fn ($item) => [
                    'kondisi_nama'  => $item->kondisiTiang?->nama,
                    'kondisi_level' => $item->kondisiTiang?->level,
                    'total'         => (int)$item->total,
                ])
                ->toArray();

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
                ->get()
                ->map(fn ($item) => [
                    'nama_operator' => $item->nama_operator,
                    'total'         => (int)$item->total,
                ])
                ->toArray();

            $totalDistrict = District::count();
            $totalArea     = DB::table('areas')->count();
            $totalSto      = Sto::whereNull('deleted_at')->count();
            $totalOperator = OperatorIsp::whereNull('deleted_at')->count();

            return [
                'total_district'           => $totalDistrict,
                'total_area'               => $totalArea,
                'total_sto'                => $totalSto,
                'total_operator'           => $totalOperator,
                'total_tiang'              => $totalTiang,
                'tiang_kondisi_ok'         => $tiangKondisiOk,
                'tiang_kondisi_nok'        => $tiangKondisiNok,
                'anomali_aktif'            => $anomaliAktif,
                'tiang_pending_verifikasi' => $tiangPendingVerifikasi,
                'per_sto'                  => $perSto,
                'per_kondisi'              => $perKondisi,
                'per_operator_top5'        => $perOperatorTop5,
            ];
        });

        return $this->success($result);
    }
}
