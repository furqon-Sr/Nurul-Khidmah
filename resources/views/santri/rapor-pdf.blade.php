<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAPOR HASIL BELAJAR - {{ $santri->nama_lengkap }} ({{ $santri->nis }})</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111827;
            background-color: #ffffff;
            margin: 0;
            padding: 20px;
            font-size: 14px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            border-bottom: 3px double #111827;
            padding-bottom: 12px;
            margin-bottom: 20px;
            position: relative;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            color: #065f46;
        }

        .header h2 {
            font-size: 16px;
            font-weight: bold;
            margin: 4px 0;
        }

        .header p {
            font-size: 12px;
            margin: 2px 0;
            color: #4b5563;
        }

        .title {
            text-align: center;
            margin-bottom: 20px;
        }

        .title h3 {
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            text-transform: uppercase;
        }

        .student-info {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .student-info td {
            padding: 4px 8px;
            vertical-align: top;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table-data th, .table-data td {
            border: 1px solid #374151;
            padding: 8px 10px;
            text-align: left;
        }

        .table-data th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            text-align: center;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }

        .summary-box {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 30px;
        }

        .attendance-table {
            width: 48%;
            border-collapse: collapse;
        }

        .attendance-table th, .attendance-table td {
            border: 1px solid #374151;
            padding: 6px 10px;
            font-size: 13px;
        }

        .signature-section {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }

        .signature-table td {
            vertical-align: top;
            width: 33%;
            padding: 10px;
        }

        .signature-space {
            height: 70px;
        }

        .no-print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #059669;
            color: #ffffff;
            border: none;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 9999;
        }

        .no-print-btn:hover {
            background-color: #047857;
        }

        @media print {
            .no-print-btn { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print-btn">Cetak / Download PDF</button>

    {{-- Kop Pesantren --}}
    <div class="header">
        <h1>PONDOK PESANTREN NURUL KHIDMAH</h1>
        <h2>SISTEM INFORMASI AKADEMIK (SIAKAD)</h2>
        <p>Jl. Raya Surabaya - Sidoarjo, Jawa Timur | Telp: (031) 889977 | Website: nurulkhidmah.org</p>
    </div>

    {{-- Judul Dokumen --}}
    <div class="title">
        <h3>KARTU HASIL STUDI (RAPOR SANTRI)</h3>
        <p style="margin-top: 4px; font-size: 13px;">Tahun Ajaran: {{ $tahunAjaran->nama_tahun_ajaran ?? '-' }} — Semester {{ $tahunAjaran->semester ?? '-' }}</p>
    </div>

    {{-- Biodata Santri --}}
    <table class="student-info">
        <tr>
            <td width="15%" class="font-bold">Nama Santri</td>
            <td width="35%">: <strong>{{ $santri->nama_lengkap }}</strong></td>
            <td width="15%" class="font-bold">Kelas</td>
            <td width="35%">: {{ $santri->kelas->nama_kelas ?? '-' }}</td>
        </tr>
        <tr>
            <td class="font-bold">NIS / ID</td>
            <td>: {{ $santri->nis }}</td>
            <td class="font-bold">Wali Kelas</td>
            <td>: {{ $santri->kelas->waliKelas->nama_lengkap ?? '-' }}</td>
        </tr>
        <tr>
            <td class="font-bold">Jenis Kelamin</td>
            <td>: {{ $santri->jenis_kelamin == 'L' ? 'Laki-Laki' : 'Perempuan' }}</td>
            <td class="font-bold">Tahun Masuk</td>
            <td>: {{ $santri->tahun_masuk ?? '-' }}</td>
        </tr>
    </table>

    {{-- Tabel Nilai --}}
    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Mata Pelajaran</th>
                <th width="25%">Pengajar (Ustaz/Ustazah)</th>
                <th width="10%">Tugas</th>
                <th width="10%">UTS</th>
                <th width="10%">UAS</th>
                <th width="10%">Akhir</th>
                <th width="8%">Grade</th>
            </tr>
        </thead>
        <tbody>
            @forelse($nilais as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->jadwalPelajaran->mataPelajaran->nama_mapel ?? '-' }}</td>
                <td>{{ $item->jadwalPelajaran->pengajar->nama_lengkap ?? '-' }}</td>
                <td class="text-center">{{ number_format($item->nilai_tugas, 2) }}</td>
                <td class="text-center">{{ number_format($item->nilai_uts, 2) }}</td>
                <td class="text-center">{{ number_format($item->nilai_uas, 2) }}</td>
                <td class="text-center font-bold">{{ number_format($item->nilai_akhir, 2) }}</td>
                <td class="text-center font-bold">{{ $item->grade ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Belum ada data nilai hasil belajar.</td>
            </tr>
            @endforelse
        </tbody>
        @if($nilais->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="6" class="text-right font-bold">RATA-RATA NILAI AKHIR (IP):</td>
                <td class="text-center font-bold" style="background-color: #f9fafb;">
                    {{ number_format($nilais->avg('nilai_akhir'), 2) }}
                </td>
                <td class="text-center font-bold" style="background-color: #f9fafb;">
                    {{ \App\Models\Nilai::calculateGrade($nilais->avg('nilai_akhir')) }}
                </td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- Ringkasan Presensi --}}
    <div style="margin-bottom: 25px;">
        <h4 style="margin-bottom: 8px; font-size: 14px; text-transform: uppercase;">Rekap Presensi & Kehadiran:</h4>
        <table class="attendance-table" style="width: 50%;">
            <tr>
                <th>Status Kehadiran</th>
                <th class="text-center">Jumlah Hari</th>
            </tr>
            <tr>
                <td>Hadir (H)</td>
                <td class="text-center font-bold">{{ $totalHadir }} Hari</td>
            </tr>
            <tr>
                <td>Izin (I)</td>
                <td class="text-center">{{ $totalIzin }} Hari</td>
            </tr>
            <tr>
                <td>Sakit (S)</td>
                <td class="text-center">{{ $totalSakit }} Hari</td>
            </tr>
            <tr>
                <td>Tanpa Keterangan / Alpa (A)</td>
                <td class="text-center">{{ $totalAlpa }} Hari</td>
            </tr>
        </table>
    </div>

    {{-- Tanda Tangan --}}
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    Mengetahui,<br>
                    Orang Tua / Wali Santri,
                    <div class="signature-space"></div>
                    ( .................................................. )
                </td>
                <td>
                    Wali Kelas,
                    <div class="signature-space"></div>
                    <strong><u>{{ $santri->kelas->waliKelas->nama_lengkap ?? '................................................' }}</u></strong><br>
                    NIY. {{ $santri->kelas->waliKelas->nomor_identitas ?? '-' }}
                </td>
                <td>
                    Surabaya, {{ date('d F Y') }}<br>
                    Pengurus Akademik Pesantren,
                    <div class="signature-space"></div>
                    <strong><u>Ust. Fahruri Anafi, M.Pd</u></strong><br>
                    Kepala Bidang Akademik
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
