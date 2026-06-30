<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnomalyLogResource;
use App\Models\AnomalyLog;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnomalyApiController extends Controller
{
    protected function success($data, string $message = 'OK'): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data, 'message' => $message]);
    }

    /**
     * GET /api/anomali/aktif
     * Daftar semua anomali yang masih aktif.
     */
    public function aktif(): JsonResponse
    {
        $data = AnomalyLog::with(['tiang:id,kode_tiang'])
            ->where('status', 'aktif')
            ->orderByDesc('detected_at')
            ->get();

        return $this->success(AnomalyLogResource::collection($data));
    }

    /**
     * POST /api/anomali/{id}/resolve
     * Tandai anomali sebagai resolved.
     */
    public function resolve(Request $request, int $id): JsonResponse
    {
        $log = AnomalyLog::findOrFail($id);

        if ($log->status === 'resolved') {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Anomali ini sudah berstatus resolved.',
            ], 422);
        }

        $log->update([
            'status'      => 'resolved',
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        $user = auth()->user();
        ActivityLog::record(
            'anomali', $log->id, 'updated',
            "{$user->getRoleDisplayName()} menyelesaikan anomali {$log->jenis_anomali} pada tiang ID {$log->tiang_id}",
        );

        // Update has_anomali tiang jika tidak ada lagi anomali aktif
        $tiang = $log->tiang;
        if ($tiang) {
            $tiang->has_anomali = AnomalyLog::where('tiang_id', $tiang->id)->where('status', 'aktif')->exists();
            $tiang->saveQuietly();
        }

        return $this->success(new AnomalyLogResource($log->fresh()), 'Anomali berhasil diselesaikan.');
    }
}
