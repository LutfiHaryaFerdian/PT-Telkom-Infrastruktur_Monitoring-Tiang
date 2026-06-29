<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TiangTelekomunikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterApiController extends Controller
{
    protected function success($data, string $message = 'OK'): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data, 'message' => $message]);
    }

    public function districts(): JsonResponse
    {
        $data = DB::table('districts')->orderBy('name')->get(['id', 'name']);
        return $this->success($data);
    }

    public function areas(Request $request): JsonResponse
    {
        $query = DB::table('areas')->orderBy('name');
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->integer('district_id'));
        }
        return $this->success($query->get(['id', 'district_id', 'name']));
    }

    public function stos(Request $request): JsonResponse
    {
        $query = DB::table('stos')->whereNull('deleted_at')->orderBy('kode');
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->integer('area_id'));
        }
        return $this->success($query->get(['id', 'area_id', 'kode', 'nama']));
    }

    public function jenisTiang(): JsonResponse
    {
        $data = DB::table('jenis_tiang')->orderBy('nama')->get(['id', 'nama', 'keterangan']);
        return $this->success($data);
    }

    public function kondisiTiang(): JsonResponse
    {
        $data = DB::table('kondisi_tiang')->orderBy('nama')->get(['id', 'nama', 'level']);
        return $this->success($data);
    }

    public function operatorIsp(): JsonResponse
    {
        $data = DB::table('operator_isp')
            ->whereNull('deleted_at')
            ->orderBy('nama_operator')
            ->get(['id', 'nama_operator', 'is_predefined']);
        return $this->success($data);
    }
}
