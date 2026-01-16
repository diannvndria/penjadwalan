<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Munaqosah;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Cache dashboard stats for 5 minutes to reduce database load
        $stats = Cache::remember('dashboard_stats', 300, function () {
            return [
                'total_mahasiswa' => Mahasiswa::count(),
                'upcoming_schedules' => Munaqosah::whereIn('status_konfirmasi', ['pending', 'dikonfirmasi'])
                    ->where('tanggal_munaqosah', '>=', Carbon::today())
                    ->count(),
                'ready_for_defense' => Mahasiswa::where('siap_sidang', true)
                    ->doesntHave('munaqosah')
                    ->count(),
            ];
        });

        return view('dashboard', $stats);
    }
}
