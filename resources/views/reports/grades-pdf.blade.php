@extends('layouts.pdf-letterhead')

@section('title', ($selectedSubject ? 'DAFTAR NILAI ' . $selectedSubject->nama_mapel . ' - ' : 'LEGER NILAI KELAS - ') . $kelas->nama_kelas)
@section('toolbar_title', ($selectedSubject ? 'Pratinjau Nilai Mapel ' . $selectedSubject->nama_mapel : 'Pratinjau Leger Nilai Lengkap') . ' — ' . $kelas->nama_kelas)

@section('styles')
<style>
    .table-leger th,
    .table-leger td {
        padding: {{ ($classSubjects->count() ?? 0) > 5 ? '4px 4px' : '5px 6px' }};
        font-size: {{ ($classSubjects->count() ?? 0) > 5 ? '10px' : '11px' }};
    }
    .table-leger th {
        font-size: {{ ($classSubjects->count() ?? 0) > 5 ? '9.5px' : '10.5px' }};
    }
    .legend-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
        margin-bottom: 14px;
    }
    .legend-table th,
    .legend-table td {
        border: 1px solid #374151;
        padding: 4px 7px;
        font-size: 10.5px;
    }
    .legend-table th {
        background-color: #f3f4f6;
        text-transform: uppercase;
        font-size: 10px;
    }
</style>
@endsection

@section('content')
@php
    $kepalaMadrasah = $siteInfo->get('kepala_madrasah_name')?->content_text ?: 'Ibu Uswatun Hasanah SP, S.Pd.I.';
    $classSubjects = $classSubjects ?? collect();
    $scoreMatrix = $scoreMatrix ?? [];
    $santriStats = $santriStats ?? [];
@endphp

@if(!empty($selectedSubject))
    {{-- MODE 1: CETAK RINCIAN NILAI SPESIFIK 1 MATA PELAJARAN --}}
    <div class="doc-title">
        <h3>DAFTAR CAPAIAN NILAI SPESIFIK MATA PELAJARAN</h3>
        <p>
            Mata Pelajaran: <strong>{{ $selectedSubject->nama_mapel }} ({{ $selectedSubject->kode_mapel }})</strong> &nbsp;|&nbsp;
            Pengajar: <strong>{{ $selectedSubjectTeacher ?? '-' }}</strong><br>
            Kelas: <strong>{{ $kelas->nama_kelas }}</strong> &nbsp;|&nbsp;
            Periode: <strong>{{ $tahunAjaran->nama_tahun_ajaran ?? '-' }} ({{ $tahunAjaran->semester ?? '-' }})</strong>
        </p>
    </div>

    @php
        $subjectScores = collect();
    @endphp
    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="13%">NIS</th>
                <th width="25%">Nama Santri</th>
                <th width="10%">Tugas (30%)</th>
                <th width="10%">UTS (30%)</th>
                <th width="10%">UAS (40%)</th>
                <th width="10%">Nilai Akhir</th>
                <th width="7%">Grade</th>
                <th width="18%">Catatan Evaluasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($santris as $index => $santri)
            @php
                $nilaiItem = $scoreMatrix[$santri->id][$selectedSubject->id] ?? null;
                if ($nilaiItem) {
                    $subjectScores->push((float) $nilaiItem->nilai_akhir);
                }
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $santri->nis }}</td>
                <td><strong>{{ $santri->nama_lengkap }}</strong></td>
                <td class="text-center">{{ $nilaiItem && $nilaiItem->nilai_tugas !== null ? number_format($nilaiItem->nilai_tugas, 2) : '-' }}</td>
                <td class="text-center">{{ $nilaiItem && $nilaiItem->nilai_uts !== null ? number_format($nilaiItem->nilai_uts, 2) : '-' }}</td>
                <td class="text-center">{{ $nilaiItem && $nilaiItem->nilai_uas !== null ? number_format($nilaiItem->nilai_uas, 2) : '-' }}</td>
                <td class="text-center font-bold">{{ $nilaiItem ? number_format($nilaiItem->nilai_akhir, 2) : '-' }}</td>
                <td class="text-center font-bold">{{ $nilaiItem->grade ?? '-' }}</td>
                <td style="font-size: 10.5px;">{{ $nilaiItem->keterangan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Belum ada data santri di kelas ini.</td>
            </tr>
            @endforelse
        </tbody>
        @if($subjectScores->isNotEmpty())
        @php
            $subjectAvg = round($subjectScores->avg(), 2);
            $subjectSum = round($subjectScores->sum(), 2);
        @endphp
        <tfoot>
            <tr>
                <td colspan="6" class="text-right font-bold">JUMLAH NILAI KELAS ({{ $subjectScores->count() }} SANTRI DINILAI):</td>
                <td class="text-center font-bold" style="background-color: #f9fafb;">{{ number_format($subjectSum, 2) }}</td>
                <td colspan="2" style="background-color: #f9fafb;"></td>
            </tr>
            <tr>
                <td colspan="6" class="text-right font-bold">RATA-RATA KELAS ({{ strtoupper($selectedSubject->nama_mapel) }}):</td>
                <td class="text-center font-bold" style="background-color: #f3f4f6;">{{ number_format($subjectAvg, 2) }}</td>
                <td class="text-center font-bold" style="background-color: #f3f4f6;">{{ \App\Models\Nilai::calculateGrade($subjectAvg) }}</td>
                <td style="background-color: #f3f4f6;"></td>
            </tr>
        </tfoot>
        @endif
    </table>
@else
    {{-- MODE 2 (DEFAULT): LEGER NILAI LENGKAP SELURUH MATA PELAJARAN + JUMLAH & RATA-RATA --}}
    <div class="doc-title">
        <h3>LEGER REKAPITULASI NILAI MATA PELAJARAN SANTRI PER KELAS</h3>
        <p>
            Kelas: <strong>{{ $kelas->nama_kelas }}</strong> &nbsp;|&nbsp;
            Wali Kelas: <strong>{{ $kelas->waliKelas->nama_lengkap ?? '-' }}</strong> &nbsp;|&nbsp;
            Periode: <strong>{{ $tahunAjaran->nama_tahun_ajaran ?? '-' }} ({{ $tahunAjaran->semester ?? '-' }})</strong>
        </p>
    </div>

    <table class="table-data table-leger">
        <thead>
            <tr>
                <th rowspan="2" width="4%">No</th>
                <th rowspan="2" width="11%">NIS</th>
                <th rowspan="2" width="22%">Nama Santri</th>
                @if($classSubjects->isNotEmpty())
                    <th colspan="{{ $classSubjects->count() }}">Nilai Akhir Spesifik Per Mata Pelajaran</th>
                @else
                    <th>Mata Pelajaran</th>
                @endif
                <th rowspan="2" width="9%">Jumlah</th>
                <th rowspan="2" width="9%">Rata-Rata</th>
                <th rowspan="2" width="7%">Grade</th>
                <th rowspan="2" width="10%">Sikap</th>
            </tr>
            <tr>
                @forelse($classSubjects as $subj)
                    <th>
                        <div>{{ $subj->kode_mapel }}</div>
                        <div style="font-size: 8.5px; font-weight: normal; text-transform: none; line-height: 1.15;">
                            {{ \Illuminate\Support\Str::limit($subj->nama_mapel, 14) }}
                        </div>
                    </th>
                @empty
                    <th style="font-weight: normal; text-transform: none;">Belum terjadwal</th>
                @endforelse
            </tr>
        </thead>
        <tbody>
            @forelse($santris as $index => $santri)
            @php
                $rowScores = $scoreMatrix[$santri->id] ?? [];
                $stats = $santriStats[$santri->id] ?? ['graded_count' => 0, 'jumlah' => 0, 'rata_rata' => 0, 'predikat' => '-'];
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $santri->nis }}</td>
                <td><strong>{{ $santri->nama_lengkap }}</strong></td>
                @forelse($classSubjects as $subj)
                    @php
                        $cellNilai = $rowScores[$subj->id] ?? null;
                    @endphp
                    <td class="text-center">
                        {{ $cellNilai ? number_format($cellNilai->nilai_akhir, 2) : '-' }}
                    </td>
                @empty
                    <td class="text-center">-</td>
                @endforelse
                <td class="text-center font-bold">{{ $stats['graded_count'] > 0 ? number_format($stats['jumlah'], 2) : '-' }}</td>
                <td class="text-center font-bold" style="background-color: #f9fafb;">{{ $stats['graded_count'] > 0 ? number_format($stats['rata_rata'], 2) : '-' }}</td>
                <td class="text-center font-bold">{{ $stats['predikat'] }}</td>
                <td class="text-center">{{ $santri->nilai_sikap ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ 7 + max(1, $classSubjects->count()) }}" class="text-center">Belum ada data santri di kelas ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($classSubjects->isNotEmpty())
        <div style="font-weight: bold; font-size: 11px; text-transform: uppercase; margin-bottom: 3px;">
            Rincian Mata Pelajaran, Pengajar Pengampu &amp; Rata-Rata Per Mata Pelajaran
        </div>
        <table class="legend-table">
            <thead>
                <tr>
                    <th width="14%" class="text-center">Kode Mapel</th>
                    <th width="36%">Nama Mata Pelajaran</th>
                    <th width="28%">Pengajar (Ustaz/Ustazah)</th>
                    <th width="11%" class="text-center">Dinilai</th>
                    <th width="11%" class="text-center">Rata-Rata</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classSubjects as $subj)
                @php
                    $mapelVals = collect($scoreMatrix)
                        ->map(fn ($row) => isset($row[$subj->id]) ? (float) $row[$subj->id]->nilai_akhir : null)
                        ->filter(fn ($v) => $v !== null);
                    $mapelAvg = $mapelVals->isNotEmpty() ? round($mapelVals->avg(), 2) : null;
                @endphp
                <tr>
                    <td class="text-center font-bold">{{ $subj->kode_mapel }}</td>
                    <td>{{ $subj->nama_mapel }}</td>
                    <td>{{ $subj->pengajar_nama }}</td>
                    <td class="text-center">{{ $mapelVals->count() }} / {{ $santris->count() }}</td>
                    <td class="text-center font-bold">{{ $mapelAvg !== null ? number_format($mapelAvg, 2) : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endif

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
