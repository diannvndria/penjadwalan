<?php

namespace App\Http\Controllers;

use App\Services\AutoScheduleService;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AutoScheduleController extends Controller
{
    protected AutoScheduleService $autoScheduleService;

    public function __construct(AutoScheduleService $autoScheduleService)
    {
        $this->autoScheduleService = $autoScheduleService;
    }

    /**
     * Auto-schedule untuk satu mahasiswa
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function scheduleStudent(Request $request): JsonResponse
    {
        $request->validate([
            'mahasiswa_id' => 'required|integer|exists:mahasiswas,id'
        ]);

        $result = $this->autoScheduleService->scheduleForMahasiswa($request->mahasiswa_id);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Auto-schedule untuk semua mahasiswa yang siap sidang
     *
     * @return JsonResponse
     */
    public function batchScheduleAll(): JsonResponse
    {
        $result = $this->autoScheduleService->batchScheduleAll();

        return response()->json($result);
    }

    /**
     * Mendapatkan preview mahasiswa yang siap untuk auto-schedule
     *
     * @return JsonResponse
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
                'count' => $readyStudents->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting ready students', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data mahasiswa'
            ], 500);
        }
    }

    /**
     * Mendapatkan konfigurasi auto-schedule
     *
     * @return JsonResponse
     */
    public function getConfiguration(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'default_duration_minutes' => 120,
                'working_hours' => [
                    'start' => '08:00',
                    'end' => '16:00'
                ],
                'working_days' => [1, 2, 3, 4, 5], // Senin-Jumat
                'search_days_range' => 7
            ]
        ]);
    }

    /**
     * Update konfigurasi auto-schedule
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateConfiguration(Request $request): JsonResponse
    {
        $request->validate([
            'duration_minutes' => 'sometimes|integer|min:30|max:480',
            'working_hours.start' => 'sometimes|date_format:H:i',
            'working_hours.end' => 'sometimes|date_format:H:i',
            'search_days_range' => 'sometimes|integer|min:1|max:30'
        ]);

        // Apply configuration to service
        if ($request->has('duration_minutes')) {
            $this->autoScheduleService->setDuration($request->duration_minutes);
        }

        if ($request->has('working_hours')) {
            $workingHours = $request->working_hours;
            if (isset($workingHours['start']) && isset($workingHours['end'])) {
                $this->autoScheduleService->setWorkingHours(
                    $workingHours['start'],
                    $workingHours['end']
                );
            }
        }

        if ($request->has('search_days_range')) {
            $this->autoScheduleService->setSearchRange($request->search_days_range);
        }

        return response()->json([
            'success' => true,
            'message' => 'Konfigurasi auto-schedule berhasil diperbarui'
        ]);
    }

    /**
     * Simulate auto-schedule (untuk testing tanpa menyimpan ke database)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function simulateSchedule(Request $request): JsonResponse
    {
        $request->validate([
            'mahasiswa_id' => 'required|integer|exists:mahasiswas,id'
        ]);

        try {
            // Get mahasiswa
            $mahasiswa = Mahasiswa::with(['dospem', 'munaqosah'])
                ->findOrFail($request->mahasiswa_id);

            if (!$mahasiswa->siap_sidang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mahasiswa belum siap sidang'
                ], 400);
            }

            if ($mahasiswa->munaqosah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mahasiswa sudah memiliki jadwal munaqosah'
                ], 400);
            }

            // Simulate finding available slot using reflection to access protected method
            $reflection = new \ReflectionClass($this->autoScheduleService);
            $method = $reflection->getMethod('findAvailableSlot');
            $method->setAccessible(true);
            
            $availableSlot = $method->invoke($this->autoScheduleService);

            if (!$availableSlot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada slot yang tersedia untuk penjadwalan',
                    'simulation' => true
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Simulasi berhasil - slot tersedia ditemukan',
                'simulation' => true,
                'data' => [
                    'mahasiswa' => $mahasiswa,
                    'available_slot' => $availableSlot
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in schedule simulation', [
                'mahasiswa_id' => $request->mahasiswa_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat simulasi: ' . $e->getMessage(),
                'simulation' => true
            ], 500);
        }
    }
}
