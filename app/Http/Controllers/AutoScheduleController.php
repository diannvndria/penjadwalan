<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Services\AutoScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AutoScheduleController extends Controller
{
    protected AutoScheduleService $autoScheduleService;

    public function __construct(AutoScheduleService $autoScheduleService)
    {
        $this->autoScheduleService = $autoScheduleService;
    }

    /**
     * Display the auto-schedule index page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('auto-schedule.index');
    }

    /**
     * Auto-schedule untuk satu mahasiswa
     */
    public function scheduleStudent(Request $request): JsonResponse
    {
        $request->validate([
            'mahasiswa_id' => 'required|integer|exists:mahasiswa,id',
        ]);

        $result = $this->autoScheduleService->scheduleForMahasiswa($request->mahasiswa_id);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Auto-schedule untuk semua mahasiswa yang siap sidang
     */
    public function batchScheduleAll(): JsonResponse
    {
        try {
            // Set a longer timeout for the request
            set_time_limit(300); // 5 minutes

            $result = $this->autoScheduleService->batchScheduleAll();

            if (! $result['success']) {
                Log::error('Batch scheduling failed', $result);

                return response()->json($result, 500);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Batch scheduling error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error during batch scheduling: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mendapatkan preview mahasiswa yang siap untuk auto-schedule
     */
    public function getReadyStudents(): JsonResponse
    {
        try {
            $readyStudents = Mahasiswa::where('siap_sidang', true)
                ->whereDoesntHave('munaqosah')
                ->with(['dospem'])
                ->select(['id', 'nim', 'nama', 'angkatan', 'judul_skripsi', 'id_dospem'])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data mahasiswa siap sidang berhasil diambil',
                'data' => $readyStudents,
                'count' => $readyStudents->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting ready students', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data mahasiswa',
            ], 500);
        }
    }

    /**
     * Mendapatkan konfigurasi auto-schedule dari session atau memberikan nilai default.
     */
    public function getConfiguration(): JsonResponse
    {
        // 1. Definisikan nilai-nilai default di satu tempat
        $defaults = [
            'default_duration_minutes' => 120,
            'working_hours' => [
                'start' => '08:00',
                'end' => '16:00',
            ],
            'working_days' => [1, 2, 3, 4, 5], // Senin-Jumat
            'search_days_range' => 7,
        ];

        // 2. Ambil konfigurasi dari session. Jika tidak ada, gunakan $defaults.
        $config = session()->get('auto_schedule_config', $defaults);

        return response()->json([
            'success' => true,
            'data' => $config,
        ]);
    }

    /**
     * Update konfigurasi auto-schedule dan simpan ke session.
     */
    public function updateConfiguration(Request $request): JsonResponse
    {
        // Validasi input
        $validated = $request->validate([
            'duration_minutes' => 'sometimes|integer|min:30|max:480',
            'working_hours.start' => 'sometimes|date_format:H:i',
            'working_hours.end' => 'sometimes|date_format:H:i',
            'search_days_range' => 'sometimes|integer|min:1|max:30',
        ]);

        // 1. Ambil konfigurasi yang ada saat ini dari session (atau default)
        $config = session()->get('auto_schedule_config', [
            'default_duration_minutes' => 120,
            'working_hours' => ['start' => '08:00', 'end' => '16:00'],
            'working_days' => [1, 2, 3, 4, 5],
            'search_days_range' => 7,
        ]);

        // 2. Timpa nilai konfigurasi dengan data baru yang sudah divalidasi
        if ($request->has('duration_minutes')) {
            $config['default_duration_minutes'] = $validated['duration_minutes'];
            $this->autoScheduleService->setDuration($validated['duration_minutes']);
        }

        if ($request->has('working_hours')) {
            $config['working_hours']['start'] = $validated['working_hours']['start'];
            $config['working_hours']['end'] = $validated['working_hours']['end'];
            $this->autoScheduleService->setWorkingHours(
                $validated['working_hours']['start'],
                $validated['working_hours']['end']
            );
        }

        if ($request->has('search_days_range')) {
            $config['search_days_range'] = $validated['search_days_range'];
            $this->autoScheduleService->setSearchRange($validated['search_days_range']);
        }

        // 3. Simpan kembali seluruh object konfigurasi yang sudah diperbarui ke dalam session
        session(['auto_schedule_config' => $config]);

        return response()->json([
            'success' => true,
            'message' => 'Konfigurasi auto-schedule berhasil diperbarui',
        ]);
    }

    /**
     * Simulate auto-schedule (untuk testing tanpa menyimpan ke database)
     */
    public function simulateSchedule(Request $request): JsonResponse
    {
        $request->validate([
            'mahasiswa_id' => 'required|integer|exists:mahasiswa,id',
        ]);

        try {
            // Get mahasiswa
            $mahasiswa = Mahasiswa::with(['dospem', 'munaqosah'])
                ->findOrFail($request->mahasiswa_id);

            if (! $mahasiswa->siap_sidang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mahasiswa belum siap sidang',
                ], 400);
            }

            if ($mahasiswa->munaqosah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mahasiswa sudah memiliki jadwal munaqosah',
                ], 400);
            }

            // Simulate finding available slot using reflection to access protected method
            $reflection = new \ReflectionClass($this->autoScheduleService);
            $method = $reflection->getMethod('findAvailableSlot');
            $method->setAccessible(true);

            $availableSlot = $method->invoke($this->autoScheduleService);

            if (! $availableSlot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada slot yang tersedia untuk penjadwalan',
                    'simulation' => true,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Simulasi berhasil - slot tersedia ditemukan',
                'simulation' => true,
                'data' => [
                    'mahasiswa' => $mahasiswa,
                    'available_slot' => $availableSlot,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error in schedule simulation', [
                'mahasiswa_id' => $request->mahasiswa_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat simulasi: '.$e->getMessage(),
                'simulation' => true,
            ], 500);
        }
    }
}
