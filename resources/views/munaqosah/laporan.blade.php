<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Jadwal Sidang</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            font-size: 11px;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #1a1a1a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            display: none;
        }

        .header p {
            font-size: 12px;
            margin: 3px 0;
        }

        .header .subtitle-main {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0 5px 0;
        }

        .header .subtitle-sub {
            font-size: 11px;
            margin: 3px 0;
        }

        .subtitle {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin: 15px 0;
            text-decoration: underline;
        }

        .info-section {
            margin-bottom: 15px;
            font-size: 11px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .info-section p {
            margin: 0;
            line-height: 2.2;
            display: table;
            width: 100%;
        }

        .info-label {
            font-weight: bold;
            display: table-cell;
            width: 130px;
            vertical-align: middle;
            padding-right: 20px;
        }

        .info-value {
            display: table-cell;
            vertical-align: middle;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            margin-top: 15px;
        }

        table thead {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
        }

        table th {
            border: 1px solid #1a1a1a;
            padding: 8px 5px;
            text-align: left;
            font-size: 10px;
        }

        table td {
            border: 1px solid #ddd;
            padding: 6px 5px;
            font-size: 10px;
        }

        table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        table tbody tr:nth-child(even) {
            background-color: #fff;
        }

        .status {
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 3px;
            text-align: center;
            font-size: 9px;
            display: inline-block;
        }

        .status-pending {
            background-color: #ffeaa7;
            color: #2d3436;
        }

        .status-dikonfirmasi {
            background-color: #55efc4;
            color: #005a2d;
        }

        .status-ditolak {
            background-color: #ff7675;
            color: #fff;
        }

        .page-break {
            page-break-after: always;
        }

        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 10px;
            text-align: right;
        }

        .total-row {
            background-color: #ecf0f1;
            font-weight: bold;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
        }

        .prioritas {
            background-color: #fff3cd;
            color: #856404;
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 9px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <h1>LARAVEL</h1>
            <p class="subtitle-main">Jadwal Sidang Munaqosah</p>
            <p class="subtitle-sub">Semua Jadwal</p>
        </div>

        <!-- INFO LAPORAN -->
        <div class="info-section">
            <p>
                <span class="info-label">Dicetak:</span>
                <span class="info-value">{{ $generatedAt->format('d-m-Y H:i:s') }}</span>
            </p>
            <p>
                <span class="info-label">Total Jadwal:</span>
                <span class="info-value"><strong>{{ $totalJadwal }}</strong></span>
            </p>
            @if($startDate || $endDate)
                <p>
                    <span class="info-label">Periode:</span>
                    <span class="info-value">
                        @if($startDate)
                            {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }}
                        @else
                            -
                        @endif
                        sampai
                        @if($endDate)
                            {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
                        @else
                            -
                        @endif
                    </span>
                </p>
            @endif
        </div>

        <!-- TABEL DATA JADWAL -->
        @if($munaqosahs->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th width="5%">NO</th>
                        <th width="20%">MAHASISWA</th>
                        <th width="10%">TANGGAL</th>
                        <th width="10%">WAKTU</th>
                        <th width="13%">PENGUJI 1</th>
                        <th width="13%">PENGUJI 2</th>
                        <th width="12%">RUANG</th>
                        <th width="12%">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($munaqosahs as $index => $munaqosah)
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $munaqosah->mahasiswa->nama ?? '-' }}</strong>
                                @if($munaqosah->mahasiswa && $munaqosah->mahasiswa->is_prioritas)
                                    <br><span class="prioritas">PRIORITAS</span>
                                @endif
                                <br>NIM: {{ $munaqosah->mahasiswa->nim ?? '-' }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($munaqosah->tanggal_munaqosah)->format('d-m-Y') }}</td>
                            <td>{{ substr($munaqosah->waktu_mulai, 0, 5) }} - {{ substr($munaqosah->waktu_selesai, 0, 5) }}</td>
                            <td>{{ $munaqosah->penguji1->nama ?? '-' }}</td>
                            <td>{{ $munaqosah->penguji2->nama ?? '-' }}</td>
                            <td>{{ $munaqosah->ruangUjian->nama ?? '-' }}</td>
                            <td style="text-align: center;">
                                <span class="status status-{{ strtolower(str_replace(' ', '-', $munaqosah->status_konfirmasi)) }}">
                                    {{ ucfirst($munaqosah->status_konfirmasi) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                <p>Tidak ada data jadwal sidang untuk periode yang dipilih.</p>
            </div>
        @endif

        <!-- FOOTER -->
        <div class="footer">
            <p>Dokumen ini digenerate otomatis oleh Sistem Penjadwalan Skripsi</p>
        </div>
    </div>
</body>
</html>
