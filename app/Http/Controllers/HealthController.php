<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    /**
     * GET /health
     * Check database, cache, and storage health.
     */
    public function check(): JsonResponse
    {
        $status = [
            'status'    => 'UP',
            'timestamp' => now()->toIso8601String(),
            'services'  => [
                'database' => 'UNKNOWN',
                'cache'    => 'UNKNOWN',
                'storage'  => 'UNKNOWN',
            ],
        ];

        $isHealthy = true;

        // 1. Check Database
        try {
            DB::connection()->getPdo();
            // Jalankan query super ringan
            DB::select('SELECT 1');
            $status['services']['database'] = 'OK';
        } catch (\Throwable $e) {
            $status['services']['database'] = 'FAIL: ' . $e->getMessage();
            $isHealthy = false;
        }

        // 2. Check Cache
        try {
            $key = 'health_check_ping';
            Cache::put($key, 'pong', 10);
            if (Cache::get($key) === 'pong') {
                $status['services']['cache'] = 'OK';
            } else {
                $status['services']['cache'] = 'FAIL: Cache write/read mismatch';
                $isHealthy = false;
            }
        } catch (\Throwable $e) {
            $status['services']['cache'] = 'FAIL: ' . $e->getMessage();
            $isHealthy = false;
        }

        // 3. Check Storage
        try {
            $filename = 'health-check.txt';
            Storage::disk('public')->put($filename, 'healthy');
            if (Storage::disk('public')->exists($filename)) {
                Storage::disk('public')->delete($filename);
                $status['services']['storage'] = 'OK';
            } else {
                $status['services']['storage'] = 'FAIL: Storage write verification failed';
                $isHealthy = false;
            }
        } catch (\Throwable $e) {
            $status['services']['storage'] = 'FAIL: ' . $e->getMessage();
            $isHealthy = false;
        }

        if (!$isHealthy) {
            $status['status'] = 'DOWN';
            return response()->json($status, 503);
        }

        return response()->json($status, 200);
    }
}
