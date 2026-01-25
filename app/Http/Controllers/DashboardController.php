<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Munaqosah;
use Carbon\Carbon;
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
                'upcoming_schedules_count' => Munaqosah::whereIn('status_konfirmasi', ['pending', 'dikonfirmasi'])
                    ->where('tanggal_munaqosah', '>=', Carbon::today())
                    ->count(),
                'ready_for_defense_count' => Mahasiswa::where('siap_sidang', true)
                    ->doesntHave('munaqosah')
                    ->count(),
            ];
        });

        // Fetch upcoming schedules (not cached to show real-time updates or cache with shorter duration)
        $upcoming_munaqosahs = Munaqosah::with(['mahasiswa', 'ruangUjian'])
            ->whereIn('status_konfirmasi', ['pending', 'dikonfirmasi'])
            ->where('tanggal_munaqosah', '>=', Carbon::today())
            ->orderBy('tanggal_munaqosah', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->limit(5)
            ->get();

        // Fetch students ready for defense (for admin view)
        $ready_students = Mahasiswa::where('siap_sidang', true)
            ->doesntHave('munaqosah')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', array_merge($stats, [
            'upcoming_munaqosahs' => $upcoming_munaqosahs,
            'ready_students' => $ready_students,
        ]));
    }
}
