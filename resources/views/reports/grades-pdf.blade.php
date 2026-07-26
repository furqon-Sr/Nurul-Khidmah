<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REKAP NILAI KELAS - {{ $kelas->nama_kelas }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; color: #111827; margin: 0; padding: 20px; font-size: 13px; line-height: 1.4; }
        .header { text-align: center; border-bottom: 3px double #111827; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; font-weight: bold; text-transform: uppercase; margin: 0; color: #065f46; }
        .header h2 { font-size: 16px; font-weight: bold; margin: 4px 0; }
        .header p { font-size: 12px; margin: 2px 0; color: #4b5563; }
        .title { text-align: center; margin-bottom: 20px; }
        .title h3 { font-size: 16px; font-weight: bold; text-decoration: underline; margin: 0; text-transform: uppercase; }
        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-data th, .table-data td { border: 1px solid #374151; padding: 6px 8px; text-align: left; }
        .table-data th { background-color: #f3f4f6; font-weight: bold; text-transform: uppercase; font-size: 11px; text-align: center; }
        .text-center { text-align: center !important; }
        .font-bold { font-weight: bold; }
        .no-print-btn { position: fixed; top: 20px; right: 20px; background-color: #059669; color: #ffffff; border: none; padding: 10px 18px; font-size: 14px; font-weight: bold; border-radius: 6px; cursor: pointer; z-index: 9999; }
        @media print { .no-print-btn { display: none; } body { padding: 0; } }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print-btn">🖨️ Cetak / Download PDF</button>

    <div class="header">
        <h1>PONDOK PESANTREN NURUL KHIDMAH</h1>
        <h2>SISTEM INFORMASI AKADEMIK (SIAKAD)</h2>
        <p>Jl. Raya Surabaya - Sidoarjo, Jawa Timur | Telp: (031) 889977 | Website: nurulkhidmah.org</p>
    </div>

    <div class="title">
        <h3>REKAPITULASI TRANSKRIP NILAI SANTRI PER KELAS</h3>
        <p style="margin-top: 4px; font-size: 13px;">Kelas: <strong>{{ $kelas->nama_kelas }}</strong> | Wali Kelas: <strong>{{ $kelas->waliKelas->nama_lengkap ?? '-' }}</strong></p>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">NIS</th>
                <th width="30%">Nama Santri</th>
                <th width="15%">Jml Mapel Dinilai</th>
                <th width="15%">Rata-Rata Nilai</th>
                <th width="13%">Grade Rata-Rata</th>
            </tr>
        </thead>
        <tbody>
            @forelse($santris as $index => $santri)
            @php
                $gList = $gradesBySantri[$santri->id] ?? collect();
                $avg = $gList->isNotEmpty() ? round($gList->avg('nilai_akhir'), 2) : 0;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $santri->nis }}</td>
                <td><strong>{{ $santri->nama_lengkap }}</strong></td>
                <td class="text-center">{{ $gList->count() }} Mapel</td>
                <td class="text-center font-bold">{{ number_format($avg, 2) }}</td>
                <td class="text-center font-bold">{{ \App\Models\Nilai::calculateGrade($avg) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Belum ada data nilai di kelas ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 40px; text-align: center;">
        <tr>
            <td width="50%">
                Wali Kelas,
                <div style="height: 70px;"></div>
                <strong><u>{{ $kelas->waliKelas->nama_lengkap ?? '................................' }}</u></strong>
            </td>
            <td width="50%">
                Surabaya, {{ date('d F Y') }}<br>
                Pengurus Akademik,
                <div style="height: 70px;"></div>
                <strong><u>Ust. Fahruri Anafi, M.Pd</u></strong>
            </td>
        </tr>
    </table>

</body>
</html>
