@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Auto-Schedule Penjadwalan Sidang
    </h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Alert Messages -->
        <div id="alert-container" class="mb-6"></div>

        <!-- Configuration Panel -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">Konfigurasi Auto-Schedule</h3>
                    <button id="editConfigBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Konfigurasi
                    </button>
                </div>
                
                <div id="configDisplay" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-gray-50 p-4 rounded">
                        <h4 class="font-medium text-gray-700">Durasi Sidang</h4>
                        <p id="durationDisplay" class="text-lg">120 menit</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded">
                        <h4 class="font-medium text-gray-700">Jam Kerja</h4>
                        <p id="workingHoursDisplay" class="text-lg">08:00 - 16:00</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded">
                        <h4 class="font-medium text-gray-700">Hari Kerja</h4>
                        <p class="text-lg">Senin - Jumat</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded">
                        <h4 class="font-medium text-gray-700">Range Pencarian</h4>
                        <p id="searchRangeDisplay" class="text-lg">7 hari</p>
                    </div>
                </div>

                <!-- Configuration Form (Hidden by default) -->
                <form id="configForm" class="hidden mt-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Durasi Sidang (menit)</label>
                            <input type="number" id="duration" name="duration_minutes" min="30" max="480" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jam Mulai</label>
                            <input type="time" id="startTime" name="working_hours[start]" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jam Selesai</label>
                            <input type="time" id="endTime" name="working_hours[end]" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="mt-4 flex space-x-2">
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Simpan Konfigurasi
                        </button>
                        <button type="button" id="cancelConfigBtn" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Ready Students Panel -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">
                        Mahasiswa Siap Sidang 
                        <span id="readyCount" class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">0</span>
                    </h3>
                    <div class="space-x-2">
                        <button id="refreshBtn" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Refresh
                        </button>
                        <button id="batchScheduleBtn" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Jadwalkan Semua
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table id="readyStudentsTable" class="min-w-full table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIM</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Angkatan</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dosen Pembimbing</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
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
        <div id="resultsPanel" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hidden">
            <div class="p-6 text-gray-900">
                <h3 class="text-lg font-medium mb-4">Hasil Penjadwalan</h3>
                <div id="resultsContent">
                    <!-- Results will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Individual Schedule Confirmation -->
<div id="scheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Konfirmasi Penjadwalan</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="modalMessage">
                    Apakah Anda yakin ingin menjadwalkan mahasiswa ini secara otomatis?
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmScheduleBtn" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-auto mr-2 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    Ya, Jadwalkan
                </button>
                <button id="cancelScheduleBtn" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-auto hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Global variables
    let currentStudentId = null;
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
    document.getElementById('confirmScheduleBtn').addEventListener('click', confirmIndividualSchedule);
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
        fetch('/auto-schedule/ready-students')
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
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-4 py-4 text-center text-gray-500">
                        Tidak ada mahasiswa yang siap sidang
                    </td>
                </tr>
            `;
            return;
        }
        
        students.forEach(student => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">${student.nim}</td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">${student.nama}</td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">${student.angkatan}</td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${student.dospem ? student.dospem.nama : 'Belum ditentukan'}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                    <button onclick="scheduleIndividual(${student.id}, '${student.nama}')" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-xs">
                        Jadwalkan
                    </button>
                    <button onclick="simulateSchedule(${student.id}, '${student.nama}')" 
                            class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-3 rounded text-xs ml-1">
                        Simulasi
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    function batchSchedule() {
        if (!confirm('Apakah Anda yakin ingin menjadwalkan semua mahasiswa yang siap sidang secara otomatis?')) {
            return;
        }
        
        showAlert('Memproses batch scheduling...', 'info');
        
        fetch('/auto-schedule/batch-schedule', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            showBatchResults(data);
            loadReadyStudents(); // Refresh the list
        })
        .catch(error => {
            console.error('Error in batch scheduling:', error);
            showAlert('Error in batch scheduling', 'error');
        });
    }
    
    function showBatchResults(data) {
        const resultsPanel = document.getElementById('resultsPanel');
        const resultsContent = document.getElementById('resultsContent');
        
        let html = `
            <div class="mb-4">
                <h4 class="text-md font-medium">Ringkasan Batch Scheduling</h4>
                <div class="grid grid-cols-3 gap-4 mt-2">
                    <div class="bg-green-100 p-3 rounded">
                        <p class="text-green-800 font-medium">Berhasil: ${data.scheduled_count}</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded">
                        <p class="text-red-800 font-medium">Gagal: ${data.failed_count}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded">
                        <p class="text-blue-800 font-medium">Total: ${data.scheduled_count + data.failed_count}</p>
                    </div>
                </div>
            </div>
        `;
        
        if (data.results && data.results.length > 0) {
            html += `
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Mahasiswa</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">NIM</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
            `;
            
            data.results.forEach(result => {
                const statusClass = result.result.success ? 'text-green-600' : 'text-red-600';
                const statusText = result.result.success ? '✅ Berhasil' : '❌ Gagal';
                
                html += `
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900">${result.mahasiswa}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">${result.nim}</td>
                        <td class="px-4 py-2 text-sm ${statusClass}">${statusText}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">${result.result.message}</td>
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
    window.scheduleIndividual = function(studentId, studentName) {
        currentStudentId = studentId;
        document.getElementById('modalMessage').textContent = 
            `Apakah Anda yakin ingin menjadwalkan mahasiswa "${studentName}" secara otomatis?`;
        showModal();
    };
    
    window.simulateSchedule = function(studentId, studentName) {
        showAlert(`Menjalankan simulasi untuk ${studentName}...`, 'info');
        
        fetch('/auto-schedule/simulate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ mahasiswa_id: studentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(`Simulasi berhasil untuk ${studentName}: ${data.message}`, 'success');
            } else {
                showAlert(`Simulasi gagal untuk ${studentName}: ${data.message}`, 'error');
            }
        })
        .catch(error => {
            console.error('Error in simulation:', error);
            showAlert('Error dalam simulasi', 'error');
        });
    };
    
    function confirmIndividualSchedule() {
        if (!currentStudentId) return;
        
        fetch('/auto-schedule/schedule-student', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ mahasiswa_id: currentStudentId })
        })
        .then(response => response.json())
        .then(data => {
            hideModal();
            if (data.success) {
                showAlert(data.message, 'success');
                loadReadyStudents(); // Refresh the list
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error in scheduling:', error);
            showAlert('Error dalam penjadwalan', 'error');
            hideModal();
        });
    }
    
    function showModal() {
        document.getElementById('scheduleModal').classList.remove('hidden');
    }
    
    function hideModal() {
        document.getElementById('scheduleModal').classList.add('hidden');
        currentStudentId = null;
    }
    
    function showAlert(message, type) {
        const alertContainer = document.getElementById('alert-container');
        const alertClass = {
            'success': 'bg-green-100 border-green-500 text-green-700',
            'error': 'bg-red-100 border-red-500 text-red-700',
            'warning': 'bg-yellow-100 border-yellow-500 text-yellow-700',
            'info': 'bg-blue-100 border-blue-500 text-blue-700'
        };
        
        const alert = document.createElement('div');
        alert.className = `border-l-4 p-4 ${alertClass[type]} mb-4`;
        alert.innerHTML = `
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm">${message}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" class="inline-flex rounded-md p-1.5 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        alertContainer.appendChild(alert);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
});
</script>
@endsection
