@extends('layouts.app')

@section('header')
    Auto-Schedule Penjadwalan Sidang
@endsection




@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Alert Messages -->
        <div id="alert-container" class="mb-6"></div>

        <!-- Configuration Panel -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 mb-6">
            <div class="p-4 sm:p-6 text-gray-900">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-cog text-indigo-600"></i>
                        Konfigurasi Auto-Schedule
                    </h3>
                    <button id="editConfigBtn" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm transition">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Konfigurasi
                    </button>
                </div>

                <div id="configDisplay" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-5 rounded-xl border border-blue-100">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-blue-600 rounded-lg">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <h4 class="font-semibold text-gray-700">Durasi Sidang</h4>
                        </div>
                        <p id="durationDisplay" class="text-2xl font-bold text-gray-800 ml-11">120 menit</p>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-5 rounded-xl border border-purple-100">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-purple-600 rounded-lg">
                                <i class="fas fa-business-time text-white"></i>
                            </div>
                            <h4 class="font-semibold text-gray-700">Jam Kerja</h4>
                        </div>
                        <p id="workingHoursDisplay" class="text-2xl font-bold text-gray-800 ml-11">08:00 - 16:00</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-5 rounded-xl border border-green-100">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-green-600 rounded-lg">
                                <i class="fas fa-calendar-week text-white"></i>
                            </div>
                            <h4 class="font-semibold text-gray-700">Hari Kerja</h4>
                        </div>
                        <p class="text-2xl font-bold text-gray-800 ml-11">Senin - Jumat</p>
                    </div>
                    <div class="bg-gradient-to-br from-orange-50 to-amber-50 p-5 rounded-xl border border-orange-100">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-orange-600 rounded-lg">
                                <i class="fas fa-search text-white"></i>
                            </div>
                            <h4 class="font-semibold text-gray-700">Range Pencarian</h4>
                        </div>
                        <p id="searchRangeDisplay" class="text-2xl font-bold text-gray-800 ml-11">7 hari</p>
                    </div>
                </div>

                <!-- Configuration Form (Hidden by default) -->
                <form id="configForm" class="hidden mt-6 bg-gradient-to-br from-gray-50 to-blue-50 p-4 sm:p-6 rounded-xl border border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        <div>
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-clock text-gray-400"></i>
                                Durasi Sidang (menit)
                            </label>
                            <input type="number" id="duration" name="duration_minutes" min="30" max="480"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-hourglass-start text-gray-400"></i>
                                Jam Mulai
                            </label>
                            <input type="time" id="startTime" name="working_hours[start]"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-hourglass-end text-gray-400"></i>
                                Jam Selesai
                            </label>
                            <input type="time" id="endTime" name="working_hours[end]"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 shadow-sm transition">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Konfigurasi
                        </button>
                        <button type="button" id="cancelConfigBtn" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-gray-500 to-gray-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-gray-600 hover:to-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 shadow-sm transition">
                            <i class="fas fa-times mr-2"></i>
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Ready Students Panel -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 mb-6">
            <div class="p-4 sm:p-6 text-gray-900">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-800 flex items-center gap-2 sm:gap-3">
                        <i class="fas fa-user-graduate text-indigo-600"></i>
                        <span class="hidden sm:inline">Mahasiswa Siap Sidang</span>
                        <span class="sm:hidden">Siap Sidang</span>
                        <span id="readyCount" class="inline-flex items-center justify-center h-7 sm:h-8 px-2 sm:px-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-xs sm:text-sm font-bold rounded-full shadow-sm">0</span>
                    </h3>
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full lg:w-auto">
                        <button id="refreshBtn" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-gray-500 to-gray-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-gray-600 hover:to-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 shadow-sm transition">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Refresh
                        </button>
                        <button id="batchScheduleBtn" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 shadow-sm transition">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Jadwalkan Semua
                        </button>
                    </div>
                </div>

                {{-- Desktop Table View --}}
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table id="readyStudentsTable" class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100/50">
                                <th class="px-6 py-4 text-left whitespace-nowrap">
                                    <div class="flex items-center gap-2 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-hashtag text-gray-400"></i>
                                        NIM
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left whitespace-nowrap">
                                    <div class="flex items-center gap-2 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-user text-gray-400"></i>
                                        Nama
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left whitespace-nowrap">
                                    <div class="flex items-center gap-2 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-graduation-cap text-gray-400"></i>
                                        Angkatan
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left whitespace-nowrap">
                                    <div class="flex items-center gap-2 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-chalkboard-teacher text-gray-400"></i>
                                        Dosen Pembimbing
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-cogs text-gray-400"></i>
                                        Aksi
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="studentsTableBody" class="bg-white divide-y divide-gray-200">
                            <!-- Will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Schedule Results Panel -->
        <div id="resultsPanel" class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 hidden">
            <div class="p-6 text-gray-900">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center gap-2 mb-4">
                    <i class="fas fa-check-circle text-green-600"></i>
                    Hasil Penjadwalan
                </h3>
                <div id="resultsContent" class="space-y-3">
                    <!-- Results will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Individual Schedule Confirmation -->
<div id="scheduleModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-4 sm:px-6 py-3 sm:py-4">
            <h3 class="text-lg sm:text-xl font-bold text-white flex items-center gap-2" id="modalTitle">
                <i class="fas fa-calendar-check"></i>
                Konfirmasi Penjadwalan
            </h3>
        </div>
        <div class="p-4 sm:p-6">
            <p class="text-gray-700 text-sm leading-relaxed" id="modalMessage">
                Apakah Anda yakin ingin menjadwalkan mahasiswa ini secara otomatis?
            </p>
        </div>
        <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 flex flex-col sm:flex-row gap-3 justify-end">
            <button id="cancelScheduleBtn" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 shadow-sm transition">
                <i class="fas fa-times mr-2"></i>
                Batal
            </button>
            <button id="confirmScheduleBtn" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm transition">
                <i class="fas fa-check mr-2"></i>
                Ya, Jadwalkan
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Global variables
    let currentStudentId = null;
    let isBatchSchedule = false;
    let configuration = {};

    // Load initial data
    loadConfiguration();
    loadReadyStudents();

    // Event listeners
    document.getElementById('editConfigBtn').addEventListener('click', showConfigForm);
    document.getElementById('cancelConfigBtn').addEventListener('click', hideConfigForm);
    document.getElementById('configForm').addEventListener('submit', saveConfiguration);
    document.getElementById('refreshBtn').addEventListener('click', loadReadyStudents);
    document.getElementById('batchScheduleBtn').addEventListener('click', batchSchedule);
    document.getElementById('confirmScheduleBtn').onclick = confirmIndividualSchedule;
    document.getElementById('cancelScheduleBtn').addEventListener('click', hideModal);

    // Functions
    function loadConfiguration() {
        fetch('/auto-schedule/configuration')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    configuration = data.data;
                    updateConfigDisplay();
                }
            })
            .catch(error => {
                console.error('Error loading configuration:', error);
                showAlert('Error loading configuration', 'error');
            });
    }

    function updateConfigDisplay() {
        document.getElementById('durationDisplay').textContent = configuration.default_duration_minutes + ' menit';
        document.getElementById('workingHoursDisplay').textContent =
            configuration.working_hours.start + ' - ' + configuration.working_hours.end;
        document.getElementById('searchRangeDisplay').textContent = configuration.search_days_range + ' hari';
    }

    function showConfigForm() {
        // Populate form with current values
        document.getElementById('duration').value = configuration.default_duration_minutes;
        document.getElementById('startTime').value = configuration.working_hours.start;
        document.getElementById('endTime').value = configuration.working_hours.end;

        document.getElementById('configDisplay').classList.add('hidden');
        document.getElementById('configForm').classList.remove('hidden');
        document.getElementById('editConfigBtn').classList.add('hidden');
    }

    function hideConfigForm() {
        document.getElementById('configDisplay').classList.remove('hidden');
        document.getElementById('configForm').classList.add('hidden');
        document.getElementById('editConfigBtn').classList.remove('hidden');
    }

    function saveConfiguration(event) {
        // Perintah ini sudah benar, untuk mencegah refresh
        event.preventDefault();

        const formData = new FormData(this);

        // Pastikan kita membuat objek `newData` dengan struktur dan nama key
        // yang SAMA PERSIS dengan yang diharapkan oleh validasi di Controller Laravel.
        const newData = {
            _method: 'PUT',

            // Key di sini 'duration_minutes' HARUS cocok dengan aturan validasi di backend.
            // Kita ambil nilainya dari input HTML yang memiliki name="duration_minutes".
            duration_minutes: parseInt(formData.get('duration_minutes')) || 0,

            // Key 'working_hours' adalah sebuah object yang berisi 'start' dan 'end'.
            // Kita ambil nilainya dari input dengan name="working_hours[start]" dan "working_hours[end]"
            working_hours: {
                start: formData.get('working_hours[start]'),
                end: formData.get('working_hours[end]')
            }

            // Jika Anda memiliki input untuk 'search_days_range', tambahkan di sini juga.
            // Contoh: search_days_range: parseInt(formData.get('search_range_input_name'))
        };

        const url = '{{ route('auto-schedule.update-configuration') }}';

        fetch(url, {
            // --- PERUBAHAN #2: UBAH METHOD MENJADI 'POST' ---
            method: 'POST',
            // ------------------------------------------------
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(newData)
        })
        .then(response => {
            // --- PERUBAHAN #3: LOGIKA ERROR HANDLING YANG DIPERBAIKI ---
            // Langsung coba parse sebagai JSON. Jika gagal, akan dilempar ke .catch()
            return response.json().then(data => {
                // Tambahkan status 'ok' dari response ke data agar bisa dicek bersamaan
                if (!response.ok) {
                    throw data; // Jika status tidak 2xx, lempar data error ke .catch()
                }
                return data;
            });
        })
        .then(data => {
            // Cek 'success' flag dari response JSON Anda
            if (data.success) {
                showAlert('Konfigurasi berhasil disimpan', 'success');
                loadConfiguration();
                hideConfigForm();
            } else {
                // Seharusnya ini ditangkap oleh .catch(), tapi sebagai cadangan
                showAlert('Gagal menyimpan: ' + (data.message || 'Error dari server'), 'error');
            }
        })
        .catch(error => {
            console.error('Error saving configuration:', error);
            // 'error' di sini adalah object JSON dari server atau error lainnya
            const errorMessage = error.message || 'Terjadi kesalahan yang tidak terduga. Cek console.';
            showAlert(`Gagal menyimpan: ${errorMessage}`, 'error');
        });
    }

    function loadReadyStudents() {
        fetch('/auto-schedule/ready-students', {
            headers: {
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateStudentsTable(data.data);
                    document.getElementById('readyCount').textContent = data.count;
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error loading students:', error);
                showAlert('Error loading students data', 'error');
            });
    }

    function updateStudentsTable(students) {
        const tbody = document.getElementById('studentsTableBody');
        tbody.innerHTML = '';

        if (students.length === 0) {
            // Desktop empty state
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">Tidak ada mahasiswa yang siap sidang</p>
                            <p class="text-gray-400 text-sm mt-2">Mahasiswa akan muncul di sini ketika mereka ditandai siap untuk sidang</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        students.forEach(student => {
            // Desktop table row
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50 transition-colors';
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium text-gray-900">${student.nim}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium text-gray-900">${student.nama}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-blue-50 text-blue-800 border border-blue-200">
                        <i class="fas fa-calendar-alt mr-1.5"></i>
                        ${student.angkatan}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${student.dospem ? `
                        <span class="text-sm text-gray-700">${student.dospem.nama}</span>
                    ` : `
                        <span class="text-sm text-gray-400 italic">Belum ditentukan</span>
                    `}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <button onclick="scheduleIndividual('${student.nim}', '${student.nama}')"
                            id="schedule-btn-${student.nim}"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-calendar-plus mr-1.5"></i>
                        Jadwalkan
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    function batchSchedule() {
        // Ganti konfirmasi bawaan dengan modal kustom
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-calendar-check"></i> Konfirmasi Penjadwalan Massal';
        document.getElementById('modalMessage').textContent = 'Apakah Anda yakin ingin menjadwalkan semua mahasiswa yang siap sidang secara otomatis?';

        // Atur event listener untuk tombol konfirmasi di modal
        // Pastikan confirmScheduleBtn sekarang akan memanggil fungsi yang berbeda atau ada logika internal
        // yang membedakan apakah itu batch atau individual.
        // Untuk sederhana, kita akan ubah event listener langsung di sini sebelum menampilkan modal.
        document.getElementById('confirmScheduleBtn').onclick = executeBatchSchedule;

        showModal(); // Tampilkan modal kustom
    }

        function executeBatchSchedule() {
        hideModal(); // Sembunyikan modal setelah konfirmasi

        showAlert('Memproses batch scheduling...', 'info');

        fetch('/auto-schedule/batch-schedule', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                if (response.headers.get('content-type')?.includes('application/json')) {
                    return response.json().then(err => { throw err; });
                }
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            showBatchResults(data);
            loadReadyStudents(); // Refresh the list

            // If there were any failures, show a more detailed message
            if (data.failed_count > 0) {
                showAlert(`Penjadwalan selesai: ${data.scheduled_count} berhasil, ${data.failed_count} gagal. Periksa detail di bawah.`, 'warning');
            } else {
                showAlert(`Berhasil menjadwalkan ${data.scheduled_count} mahasiswa!`, 'success');
            }
        })
        .catch(error => {
            console.error('Error in batch scheduling:', error);
            const errorMessage = error.message || 'Terjadi kesalahan dalam batch scheduling';
            showAlert(errorMessage, 'error');
        });
    }

    function showBatchResults(data) {
        const resultsPanel = document.getElementById('resultsPanel');
        const resultsContent = document.getElementById('resultsContent');

        let html = `
            <div class="bg-gradient-to-br from-gray-50 to-blue-50 p-6 rounded-xl border border-gray-200 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-bar text-indigo-600"></i>
                    Ringkasan Batch Scheduling
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-5 rounded-xl border border-green-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-700 mb-1">Berhasil</p>
                                <p class="text-3xl font-bold text-green-800">${data.scheduled_count}</p>
                            </div>
                            <div class="p-3 bg-green-600 rounded-lg">
                                <i class="fas fa-check text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-red-50 to-rose-50 p-5 rounded-xl border border-red-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-red-700 mb-1">Gagal</p>
                                <p class="text-3xl font-bold text-red-800">${data.failed_count}</p>
                            </div>
                            <div class="p-3 bg-red-600 rounded-lg">
                                <i class="fas fa-times text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-5 rounded-xl border border-blue-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-700 mb-1">Total</p>
                                <p class="text-3xl font-bold text-blue-800">${data.scheduled_count + data.failed_count}</p>
                            </div>
                            <div class="p-3 bg-blue-600 rounded-lg">
                                <i class="fas fa-users text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        if (data.results && data.results.length > 0) {
            html += `
                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100/50">
                                <th class="px-6 py-4 text-left">
                                    <div class="flex items-center gap-2 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-user text-gray-400"></i>
                                        Mahasiswa
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left">
                                    <div class="flex items-center gap-2 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-hashtag text-gray-400"></i>
                                        NIM
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left">
                                    <div class="flex items-center gap-2 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-info-circle text-gray-400"></i>
                                        Status
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left">
                                    <div class="flex items-center gap-2 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-comment text-gray-400"></i>
                                        Keterangan
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
            `;

            data.results.forEach(result => {
                const statusBadge = result.result.success
                    ? '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200"><i class="fas fa-check-circle mr-1"></i> Berhasil</span>'
                    : '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200"><i class="fas fa-times-circle mr-1"></i> Gagal</span>';

                html += `
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">${result.mahasiswa}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">${result.nim}</td>
                        <td class="px-6 py-4">${statusBadge}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">${result.result.message}</td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;
        }

        resultsContent.innerHTML = html;
        resultsPanel.classList.remove('hidden');

        showAlert(data.message, data.scheduled_count > 0 ? 'success' : 'warning');
    }

    // Global functions for button clicks
    window.scheduleIndividual = function(studentNim, studentName) {
        // Disable the button immediately to prevent double-clicks
        const button = document.getElementById(`schedule-btn-${studentNim}`);
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i>Memproses...';
        }

        currentStudentId = studentNim;
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-calendar-check"></i> Konfirmasi Penjadwalan';
        document.getElementById('modalMessage').textContent =
            `Apakah Anda yakin ingin menjadwalkan mahasiswa "${studentName}" secara otomatis?`;
        showModal();
    };

    function confirmIndividualSchedule() {
        if (!currentStudentId) return;

        fetch('/auto-schedule/schedule-student', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Cache-Control': 'no-cache' // Prevent caching
            },
            body: JSON.stringify({ mahasiswa_id: currentStudentId })
        })
        .then(response => {
            // Parse JSON first before checking status
            return response.json().then(data => {
                return { status: response.status, data: data };
            });
        })
        .then(result => {
            const { status, data } = result;

            if (data.success) {
                hideModal();
                showAlert(data.message, 'success');
                // Add a small delay before refreshing to ensure DB commit completes
                setTimeout(() => {
                    loadReadyStudents();
                }, 500); // 500ms delay
            } else {
                // Only show error if it's not an "already scheduled" validation error
                // This prevents showing redundant errors when refreshing the list
                if (status === 400 && data.message && data.message.includes('sudah memiliki jadwal')) {
                    // Student already has schedule - this is expected after successful scheduling
                    // Silently refresh the list instead of showing error
                    hideModal();
                    setTimeout(() => {
                        loadReadyStudents();
                    }, 500);
                } else {
                    // Re-enable the button before hiding modal for failed schedules
                    const button = document.getElementById(`schedule-btn-${currentStudentId}`);
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-calendar-plus mr-1.5"></i>Jadwalkan';
                    }
                    hideModal();
                    showAlert(data.message, 'error');
                }
            }
        })
        .catch(error => {
            console.error('Error in scheduling:', error);
            // Re-enable the button on error
            const button = document.getElementById(`schedule-btn-${currentStudentId}`);
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-calendar-plus mr-1.5"></i>Jadwalkan';
            }
            hideModal();
            showAlert('Error dalam penjadwalan', 'error');
        });
    }

    function showModal() {
        const modal = document.getElementById('scheduleModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function hideModal() {
        const modal = document.getElementById('scheduleModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');

        // Re-enable the button if user cancels (only if student hasn't been scheduled)
        if (currentStudentId) {
            const button = document.getElementById(`schedule-btn-${currentStudentId}`);
            if (button && button.disabled) {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-calendar-plus mr-1.5"></i>Jadwalkan';
            }
        }

        currentStudentId = null;

        // Reset the onclick handler for confirmScheduleBtn to its default individual schedule behavior
        document.getElementById('confirmScheduleBtn').onclick = confirmIndividualSchedule;
    }

    function showAlert(message, type) {
        const alertContainer = document.getElementById('alert-container');
        const alertConfig = {
            'success': {
                bg: 'bg-green-50',
                border: 'border-green-200',
                text: 'text-green-800',
                icon: 'fa-check-circle text-green-600'
            },
            'error': {
                bg: 'bg-red-50',
                border: 'border-red-200',
                text: 'text-red-800',
                icon: 'fa-exclamation-circle text-red-600'
            },
            'warning': {
                bg: 'bg-yellow-50',
                border: 'border-yellow-200',
                text: 'text-yellow-800',
                icon: 'fa-exclamation-triangle text-yellow-600'
            },
            'info': {
                bg: 'bg-blue-50',
                border: 'border-blue-200',
                text: 'text-blue-800',
                icon: 'fa-info-circle text-blue-600'
            }
        };

        const config = alertConfig[type] || alertConfig['info'];

        const alert = document.createElement('div');
        alert.className = `${config.bg} border-l-4 ${config.border} ${config.text} p-4 rounded-lg shadow-sm mb-4 flex items-start gap-3`;
        alert.innerHTML = `
            <i class="fas ${config.icon} text-xl mt-0.5"></i>
            <div class="flex-1">
                <p class="font-medium">${message}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        `;

        alertContainer.appendChild(alert);

        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
});
</script>
@endsection
