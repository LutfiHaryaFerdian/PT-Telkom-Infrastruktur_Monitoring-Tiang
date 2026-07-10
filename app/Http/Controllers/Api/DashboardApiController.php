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

            $tiangAnomaliCount = (clone $baseQuery)->where('has_anomali', true)->count();
            $tiangPendingVerifikasi = (clone $baseQuery)->where('status_verifikasi', 'pending')->count();

            $anomaliPerSto = (clone $baseQuery)
                ->where('has_anomali', true)
                ->selectRaw('sto_id, COUNT(*) as total')
                ->groupBy('sto_id')
                ->pluck('total', 'sto_id')
                ->all();

            $perSto = (clone $baseQuery)
                ->selectRaw('sto_id, COUNT(*) as total')
                ->with('sto:id,kode,nama')
                ->groupBy('sto_id')
                ->orderByDesc('total')
                ->get()
                ->map(function ($item) use ($anomaliPerSto) {
                    $anomaliCount = $anomaliPerSto[$item->sto_id] ?? 0;
                    $totalTiangSto = (int)$item->total;
                    $anomaliPercent = $totalTiangSto > 0 ? min(100.0, round(($anomaliCount / $totalTiangSto) * 100, 2)) : 0.0;
                    return [
                        'sto_kode'        => $item->sto?->kode,
                        'sto_nama'        => $item->sto?->nama,
                        'total'           => $totalTiangSto,
                        'sto'             => $item->sto?->kode,
                        'total_tiang'     => $totalTiangSto,
                        'anomali'         => $anomaliCount,
                        'anomali_percent' => $anomaliPercent,
                    ];
                })
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
                    'nama'          => $item->kondisiTiang?->nama,
                    'jumlah'        => (int)$item->total,
                    'percent'       => $totalTiang > 0 ? round(((int)$item->total / $totalTiang) * 100, 2) : 0.0,
                ])
                ->toArray();

            $operatorTindakLanjut = DB::table('tiang_operator')
                ->join('operator_isp', 'operator_isp.id', '=', 'tiang_operator.operator_id')
                ->join('tiang_telekomunikasi', 'tiang_telekomunikasi.id', '=', 'tiang_operator.tiang_id')
                ->whereNull('tiang_telekomunikasi.deleted_at')
                ->whereNull('operator_isp.deleted_at')
                ->when($tiangIds->isNotEmpty(), fn($q) => $q->whereIn('tiang_operator.tiang_id', $tiangIds))
                ->whereIn('tiang_operator.status_tindaklanjut', ['belum_disurati', 'perlu_followup'])
                ->selectRaw("
                    operator_isp.nama_operator,
                    COUNT(CASE WHEN tiang_operator.status_tindaklanjut = 'belum_disurati' THEN 1 END) as belum_disurati,
                    COUNT(CASE WHEN tiang_operator.status_tindaklanjut = 'perlu_followup' THEN 1 END) as perlu_followup,
                    COUNT(*) as total
                ")
                ->groupBy('operator_isp.nama_operator')
                ->orderByDesc('total')
                ->get()
                ->map(fn ($item) => [
                    'nama_operator' => $item->nama_operator,
                    'belum_disurati' => (int)$item->belum_disurati,
                    'perlu_followup' => (int)$item->perlu_followup,
                    'total'          => (int)$item->total,
                ])
                ->toArray();

            $totalDistrict = District::count();
            $totalArea     = DB::table('areas')->count();
            $totalSto      = Sto::whereNull('deleted_at')->count();
            $totalOperator = OperatorIsp::whereNull('deleted_at')->count();

            // Breakdown Verifikasi
            $verifikasiCounts = (clone $baseQuery)
                ->selectRaw('status_verifikasi, COUNT(*) as total')
                ->groupBy('status_verifikasi')
                ->pluck('total', 'status_verifikasi')
                ->all();

            $verifikasiBreakdown = [
                'ok' => [
                    'jumlah' => $verifikasiCounts['ok'] ?? 0,
                    'percent' => $totalTiang > 0 ? round((($verifikasiCounts['ok'] ?? 0) / $totalTiang) * 100, 2) : 0.0,
                ],
                'pending' => [
                    'jumlah' => $verifikasiCounts['pending'] ?? 0,
                    'percent' => $totalTiang > 0 ? round((($verifikasiCounts['pending'] ?? 0) / $totalTiang) * 100, 2) : 0.0,
                ],
                'ditolak' => [
                    'jumlah' => $verifikasiCounts['ditolak'] ?? 0,
                    'percent' => $totalTiang > 0 ? round((($verifikasiCounts['ditolak'] ?? 0) / $totalTiang) * 100, 2) : 0.0,
                ],
                'double_input' => [
                    'jumlah' => $verifikasiCounts['double_input'] ?? 0,
                    'percent' => $totalTiang > 0 ? round((($verifikasiCounts['double_input'] ?? 0) / $totalTiang) * 100, 2) : 0.0,
                ],
            ];

            // Breakdown Legalitas ISP
            $tiangOperatorBase = DB::table('tiang_operator')
                ->join('tiang_telekomunikasi', 'tiang_telekomunikasi.id', '=', 'tiang_operator.tiang_id')
                ->whereNull('tiang_telekomunikasi.deleted_at')
                ->when($tiangIds->isNotEmpty(), fn($q) => $q->whereIn('tiang_operator.tiang_id', $tiangIds));

            $totalTiangOperators = (clone $tiangOperatorBase)->count();
            $legalitasCounts = (clone $tiangOperatorBase)
                ->selectRaw('status_legalitas, COUNT(*) as total')
                ->groupBy('status_legalitas')
                ->pluck('total', 'status_legalitas')
                ->all();

            $tindakLanjutCounts = (clone $tiangOperatorBase)
                ->selectRaw('status_tindaklanjut, COUNT(*) as total')
                ->groupBy('status_tindaklanjut')
                ->pluck('total', 'status_tindaklanjut')
                ->all();

            $legalitasBreakdown = [
                'legal' => [
                    'jumlah' => $legalitasCounts['legal'] ?? 0,
                    'percent' => $totalTiangOperators > 0 ? round((($legalitasCounts['legal'] ?? 0) / $totalTiangOperators) * 100, 2) : 0.0,
                ],
                'perlu_verifikasi' => [
                    'jumlah' => $legalitasCounts['perlu_verifikasi'] ?? 0,
                    'percent' => $totalTiangOperators > 0 ? round((($legalitasCounts['perlu_verifikasi'] ?? 0) / $totalTiangOperators) * 100, 2) : 0.0,
                ],
                'ilegal' => [
                    'jumlah' => $legalitasCounts['ilegal'] ?? 0,
                    'percent' => $totalTiangOperators > 0 ? round((($legalitasCounts['ilegal'] ?? 0) / $totalTiangOperators) * 100, 2) : 0.0,
                ],
            ];

            $perluFollowup = (clone $tiangOperatorBase)->where('tiang_operator.status_tindaklanjut', 'perlu_followup')->count();

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
                'operator_tindak_lanjut'   => $operatorTindakLanjut,
                // New additions:
                'kondisi_nok'              => $tiangKondisiNok,
                'kondisi_nok_percent'      => $totalTiang > 0 ? round(($tiangKondisiNok / $totalTiang) * 100, 2) : 0.0,
                'anomali_percent'          => $totalTiang > 0 ? min(100.0, round(($tiangAnomaliCount / $totalTiang) * 100, 2)) : 0.0,
                'pending_verifikasi'       => $tiangPendingVerifikasi,
                'pending_percent'          => $totalTiang > 0 ? round(($tiangPendingVerifikasi / $totalTiang) * 100, 2) : 0.0,
                'verifikasi_breakdown'     => $verifikasiBreakdown,
                'legalitas_isp_breakdown'  => $legalitasBreakdown,
                'perlu_followup'           => $perluFollowup,
                'tindaklanjut_counts'      => [
                    'belum_disurati' => (int)($tindakLanjutCounts['belum_disurati'] ?? 0),
                    'sudah_disurati' => (int)($tindakLanjutCounts['sudah_disurati'] ?? 0),
                    'ada_balasan'    => (int)($tindakLanjutCounts['ada_balasan'] ?? 0),
                    'perlu_followup' => (int)($tindakLanjutCounts['perlu_followup'] ?? 0),
                    'selesai'        => (int)($tindakLanjutCounts['selesai'] ?? 0),
                ],
            ];
        });

        return $this->success($result);
    }
}
