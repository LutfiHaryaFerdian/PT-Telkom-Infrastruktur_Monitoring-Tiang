<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Halaman dashboard utama.
     * Filter dari session + query param.
     * Chart dan peta diisi via API (/api/dashboard/stats, /api/tiang/map).
     */
    public function index(Request $request): View
    {
        // Persist filter ke session untuk UI, tapi API selalu terima parameter
        if ($request->hasAny(['district_id', 'area_id', 'sto_id', 'date_from', 'date_to'])) {
            $request->session()->put('dashboard_filter', $request->only([
                'district_id', 'area_id', 'sto_id', 'date_from', 'date_to',
            ]));
        }

        $filter = $request->session()->get('dashboard_filter', []);

        return view('dashboard', compact('filter'));
    }
}
