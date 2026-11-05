@extends('layouts.pdf-letterhead')

@section('title', 'REKAP PRESENSI KELAS - ' . $kelas->nama_kelas)
@section('toolbar_title', 'Pratinjau Rekap Presensi Kelas — ' . $kelas->nama_kelas)

@section('content')
@php
    $kepalaMadrasah = $siteInfo->get('kepala_madrasah_name')?->content_text ?: 'Ibu Uswatun Hasanah SP, S.Pd.I.';
@endphp

    <div class="doc-title">
        <h3>REKAPITULASI PRESENSI KEHADIRAN SANTRI PER KELAS</h3>
        <p>
            Kelas: <strong>{{ $kelas->nama_kelas }}</strong> &nbsp;|&nbsp;
            Wali Kelas: <strong>{{ $kelas->waliKelas->nama_lengkap ?? '-' }}</strong> &nbsp;|&nbsp;
            Periode: <strong>{{ $tahunAjaran->nama_tahun_ajaran ?? '-' }} ({{ $tahunAjaran->semester ?? '-' }})</strong>
        </p>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="13%">NIS</th>
                <th width="27%">Nama Santri</th>
                <th width="7%">H</th>
                <th width="7%">S</th>
                <th width="7%">I</th>
                <th width="7%">A</th>
                <th width="7%">T</th>
                <th width="7%">B</th>
                <th width="10%">Catatan</th>
                <th width="8%">% Hadir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($santris as $index => $santri)
            @php
                $s = $absensiSummary[$santri->id] ?? ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'terlambat' => 0, 'bolos' => 0, 'catatan_count' => 0];
                $tot = $s['hadir'] + $s['izin'] + $s['sakit'] + $s['alpa'] + ($s['terlambat'] ?? 0) + ($s['bolos'] ?? 0);
                $pct = $tot > 0 ? round(($s['hadir'] / $tot) * 100, 1) : 0;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $santri->nis }}</td>
                <td><strong>{{ $santri->nama_lengkap }}</strong></td>
                <td class="text-center">{{ $s['hadir'] }}</td>
                <td class="text-center">{{ $s['sakit'] }}</td>
                <td class="text-center">{{ $s['izin'] }}</td>
                <td class="text-center">{{ $s['alpa'] }}</td>
                <td class="text-center">{{ $s['terlambat'] ?? 0 }}</td>
                <td class="text-center">{{ $s['bolos'] ?? 0 }}</td>
                <td class="text-center">{{ $s['catatan_count'] > 0 ? $s['catatan_count'] . ' Catatan' : '-' }}</td>
                <td class="text-center font-bold">{{ $pct }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center">Belum ada data santri aktif di kelas ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td width="50%">
                    Mengetahui,<br>
                    Kepala Madrasah / Pimpinan Pondok,
                    <div class="signature-space"></div>
                    <strong><u>{{ $kepalaMadrasah }}</u></strong>
                </td>
                <td width="50%">
                    Lamongan, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    Wali Kelas {{ $kelas->nama_kelas }},
                    <div class="signature-space"></div>
                    <strong><u>{{ $kelas->waliKelas->nama_lengkap ?? '........................................' }}</u></strong><br>
                    NIY. {{ $kelas->waliKelas->nomor_identitas ?? '-' }}
                </td>
            </tr>
        </table>
    </div>
@endsection
