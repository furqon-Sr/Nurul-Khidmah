@extends('layouts.pdf-letterhead')

@section('title', 'RINGKASAN LAPORAN EKSEKUTIF PIMPINAN - PONPES NURUL KHIDMAH')
@section('toolbar_title', 'Pratinjau Ringkasan Laporan Track Record Eksekutif Pimpinan')

@section('styles')
<style>
    .kpi-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
    }
    .kpi-table td {
        border: 1px solid #1f2937;
        padding: 7px 10px;
        width: 25%;
        text-align: center;
    }
    .kpi-label {
        font-size: 10px;
        text-transform: uppercase;
        color: #4b5563;
        font-weight: bold;
    }
    .kpi-val {
        font-size: 16px;
        font-weight: bold;
        color: #065f46;
        margin-top: 2px;
    }
    .section-subtitle {
        font-weight: bold;
        font-size: 12px;
        text-transform: uppercase;
        margin: 10px 0 5px 0;
        color: #111827;
    }
</style>
@endsection

@section('content')
@php
    $pimpinanPondok = $siteInfo->get('pimpinan_pondok_name')?->content_text
        ?: ($siteInfo->get('about_kyai_name')?->content_text ?: 'KH. Imam Machmudi');
    $kepalaMadrasah = $siteInfo->get('kepala_madrasah_name')?->content_text ?: 'Ibu Uswatun Hasanah SP, S.Pd.I.';
@endphp

    <div class="doc-title">
        <h3>LAPORAN EKSEKUTIF TRACK RECORD &amp; EVALUASI AKADEMIK PESANTREN</h3>
        <p>
            Periode Aktif: <strong>{{ $activeYear->nama_tahun_ajaran ?? '-' }} (Semester {{ $activeYear->semester ?? '-' }})</strong>
            &nbsp;|&nbsp; Dicetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} WIB
        </p>
    </div>

    {{-- 1. Ringkasan Indikator Makro --}}
    <div class="section-subtitle">I. Ringkasan Indikator Makro Yayasan &amp; Pesantren</div>
    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-label">Santri Aktif / Lulus</div>
                <div class="kpi-val">{{ $totalSantriAktif }} Aktif / {{ $totalSantriLulus }} Lulus</div>
            </td>
            <td>
                <div class="kpi-label">Ustaz &amp; Rombel</div>
                <div class="kpi-val">{{ $totalPengajar }} Ustaz / {{ $totalKelas }} Kelas</div>
            </td>
            <td>
                <div class="kpi-label">Kehadiran &amp; Rata Nilai</div>
                <div class="kpi-val">{{ $attendancePercentage }}% &nbsp;|&nbsp; {{ number_format($averageGrade, 2) }}</div>
            </td>
            <td>
                <div class="kpi-label">Ketuntasan Nilai Rapor</div>
                <div class="kpi-val">{{ $gradeCompletionPercentage }}% ({{ $filledTotalEntries }}/{{ $expectedTotalEntries }})</div>
            </td>
        </tr>
    </table>

    {{-- 2. Track Record PPDB Tahunan --}}
    <div class="section-subtitle">II. Rekam Jejak Penerimaan Santri Baru (PPDB) per Tahun Ajaran</div>
    <table class="table-data">
        <thead>
            <tr>
                <th width="6%">No</th>
                <th width="26%">Tahun Ajaran</th>
                <th width="17%">Total Pendaftar</th>
                <th width="17%">Diterima (Accepted)</th>
                <th width="17%">Menunggu (Pending)</th>
                <th width="17%">Ditolak (Rejected)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ppdbTrends as $idx => $row)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-center font-bold">{{ $row->periode }}</td>
                <td class="text-center font-bold">{{ $row->total_pendaftar }} Calon</td>
                <td class="text-center font-bold">{{ $row->diterima }} Santri</td>
                <td class="text-center">{{ $row->pending }}</td>
                <td class="text-center">{{ $row->ditolak }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Belum ada data riwayat PPDB.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- 3. Rekap Evaluasi Akademik & Ketuntasan Input Rapor per Kelas --}}
    <div class="section-subtitle">III. Rekapitulasi Kehadiran, Rata-Rata Nilai &amp; Progres Rapor per Kelas</div>
    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="22%">Nama Kelas</th>
                <th width="25%">Wali Kelas</th>
                <th width="12%">Santri Aktif</th>
                <th width="12%">% Hadir</th>
                <th width="12%">Rata Nilai</th>
                <th width="12%">Input Rapor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($classSummaries as $idx => $c)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td><strong>{{ $c['nama_kelas'] }}</strong></td>
                <td>{{ $c['wali_kelas'] }}</td>
                <td class="text-center">{{ $c['jumlah_santri'] }}</td>
                <td class="text-center">{{ $c['attendance_pct'] }}%</td>
                <td class="text-center font-bold">{{ $c['avg_grade'] > 0 ? number_format($c['avg_grade'], 2) : '-' }}</td>
                <td class="text-center font-bold">{{ $c['completion_pct'] }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Belum ada data kelas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pengesahan Laporan Eksekutif --}}
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td width="50%">
                    Mengetahui,<br>
                    Pengasuh Pondok Pesantren Nurul Khidmah,
                    <div class="signature-space"></div>
                    <strong><u>{{ $pimpinanPondok }}</u></strong>
                </td>
                <td width="50%">
                    Lamongan, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    Kepala Madrasah,
                    <div class="signature-space"></div>
                    <strong><u>{{ $kepalaMadrasah }}</u></strong>
                </td>
            </tr>
        </table>
    </div>
@endsection
