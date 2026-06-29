<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImportHistory;
use Illuminate\Http\JsonResponse;

class ImportApiController extends Controller
{
    protected function success($data, string $message = 'OK'): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data, 'message' => $message]);
    }

    /**
     * GET /api/import/{id}/progress
     * Polling endpoint — frontend polling setiap 3 detik.
     */
    public function progress(int $id): JsonResponse
    {
        $history = ImportHistory::findOrFail($id);

        return $this->success([
            'id'               => $history->id,
            'status'           => $history->status,
            'progress_percent' => $history->progress_percent,
            'total_rows'       => $history->total_rows,
            'success_rows'     => $history->success_rows,
            'failed_rows'      => $history->failed_rows,
            'started_at'       => $history->started_at?->toISOString(),
            'finished_at'      => $history->finished_at?->toISOString(),
        ]);
    }
}
