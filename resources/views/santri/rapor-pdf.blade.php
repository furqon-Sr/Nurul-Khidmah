@extends('layouts.pdf-letterhead')

@section('title', 'RAPOR HASIL BELAJAR - ' . $santri->nama_lengkap . ' (' . $santri->nis . ')')
@section('toolbar_title', 'Pratinjau Lembar Rapor Santri — ' . $santri->nama_lengkap . ' (' . $santri->nis . ')')

@section('styles')
<style>
    .student-info {
        width: 100%;
        margin-bottom: 12px;
        border-collapse: collapse;
        font-size: 12px;
    }
    .student-info td {
        padding: 2.5px 6px;
        vertical-align: top;
    }
    .eval-grid {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
    }
    .eval-grid td.panel {
        vertical-align: top;
        padding: 0;
    }
    .sub-table {
        width: 100%;
        border-collapse: collapse;
    }
    .sub-table th, .sub-table td {
        border: 1px solid #1f2937;
        padding: 5px 8px;
        font-size: 11.5px;
    }
    .sub-table th {
        background-color: #f3f4f6;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 10.5px;
        text-align: left;
    }
</style>
@endsection

@section('content')
@php
    $kelasRapor = $kelasPeriode ?? $santri->kelas;
    $kepalaMadrasah = $kelasRapor?->educationalUnit?->headmaster?->name ?: ($siteInfo->get('kepala_madrasah_name')?->content_text ?: 'Ibu Uswatun Hasanah SP, S.Pd.I.');

    if (isset($subjectRows) && $subjectRows instanceof \Illuminate\Support\Collection) {
        $rows = $subjectRows;
    } else {
        $rows = ($nilais ?? collect())->map(fn ($item) => (object) [
            'kode_mapel' => $item->jadwalPelajaran->mataPelajaran->kode_mapel ?? '-',
            'nama_mapel' => $item->jadwalPelajaran->mataPelajaran->nama_mapel ?? '-',
            'pengajar_nama' => $item->jadwalPelajaran->pengajar->nama_lengkap ?? '-',
            'has_nilai' => true,
            'nilai_tugas' => $item->nilai_tugas,
            'nilai_uts' => $item->nilai_uts,
            'nilai_uas' => $item->nilai_uas,
            'nilai_akhir' => $item->nilai_akhir,
            'grade' => $item->grade,
            'keterangan' => $item->keterangan,
        ]);
    }

    $gradedRows = $rows->where('has_nilai', true);
    $computedJumlah = isset($jumlahNilai)
        ? (float) $jumlahNilai
        : ($gradedRows->isNotEmpty() ? round((float) $gradedRows->sum('nilai_akhir'), 2) : 0.0);
    $computedRata = isset($rataRata)
        ? (float) $rataRata
        : ($gradedRows->isNotEmpty() ? round((float) $gradedRows->avg('nilai_akhir'), 2) : 0.0);
@endphp

    {{-- Judul Dokumen --}}
    <div class="doc-title">
        <h3>LAPORAN HASIL BELAJAR SANTRI (RAPOR)</h3>
        <p>Tahun Ajaran: <strong>{{ $tahunAjaran->nama_tahun_ajaran ?? '-' }}</strong> — Semester <strong>{{ $tahunAjaran->semester ?? '-' }}</strong></p>
    </div>

    {{-- Biodata Santri --}}
    <table class="student-info">
        <tr>
            <td width="17%" class="font-bold">Nama Santri</td>
            <td width="35%">: <strong>{{ $santri->nama_lengkap }}</strong></td>
            <td width="16%" class="font-bold">Kelas</td>
            <td width="32%">: {{ $kelasRapor->nama_kelas ?? '-' }}</td>
        </tr>
        <tr>
            <td class="font-bold">NIS</td>
            <td>: {{ $santri->nis }}</td>
            <td class="font-bold">Wali Kelas</td>
            <td>: {{ $kelasRapor->waliKelas->nama_lengkap ?? '-' }}</td>
        </tr>
        <tr>
            <td class="font-bold">Jenis Kelamin</td>
            <td>: {{ $santri->genderLabel() }}</td>
            <td class="font-bold">Orang Tua / Wali</td>
            <td>: {{ $santri->nama_wali ?? '-' }}</td>
        </tr>
    </table>

    {{-- A. Capaian Nilai Spesifik Per Mata Pelajaran Akademik & Diniyah --}}
    <div style="font-weight: bold; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">
        A. Capaian Nilai Spesifik Per Mata Pelajaran Akademik &amp; Diniyah
    </div>
    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="27%">Mata Pelajaran</th>
                <th width="20%">Pengajar (Ustaz/Ustazah)</th>
                <th width="8%">Tugas<br><span style="font-size:8.5px; font-weight:normal;">(30%)</span></th>
                <th width="8%">UTS<br><span style="font-size:8.5px; font-weight:normal;">(30%)</span></th>
                <th width="8%">UAS<br><span style="font-size:8.5px; font-weight:normal;">(40%)</span></th>
                <th width="9%">Nilai Akhir</th>
                <th width="6%">Grade</th>
                <th width="16%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $row->nama_mapel }}</strong>
                    @if(!empty($row->kode_mapel) && $row->kode_mapel !== '-')
                        <span style="font-size: 10px; color: #4b5563;">({{ $row->kode_mapel }})</span>
                    @endif
                </td>
                <td>{{ $row->pengajar_nama }}</td>
                <td class="text-center">{{ $row->has_nilai && $row->nilai_tugas !== null ? number_format($row->nilai_tugas, 2) : '-' }}</td>
                <td class="text-center">{{ $row->has_nilai && $row->nilai_uts !== null ? number_format($row->nilai_uts, 2) : '-' }}</td>
                <td class="text-center">{{ $row->has_nilai && $row->nilai_uas !== null ? number_format($row->nilai_uas, 2) : '-' }}</td>
                <td class="text-center font-bold">{{ $row->has_nilai && $row->nilai_akhir !== null ? number_format($row->nilai_akhir, 2) : '-' }}</td>
                <td class="text-center font-bold">{{ $row->has_nilai ? ($row->grade ?? '-') : '-' }}</td>
                <td style="font-size: 11px;">
                    @if($row->has_nilai)
                        {{ $row->keterangan ?: '-' }}
                    @else
                        <span style="color: #6b7280; font-style: italic;">Belum diinput</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Belum ada mata pelajaran / nilai pada periode semester ini.</td>
            </tr>
            @endforelse
        </tbody>
        @if($gradedRows->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="6" class="text-right font-bold">JUMLAH NILAI AKADEMIK ({{ $gradedRows->count() }} MATA PELAJARAN):</td>
                <td class="text-center font-bold" style="background-color: #f9fafb;">
                    {{ number_format($computedJumlah, 2) }}
                </td>
                <td colspan="2" style="background-color: #f9fafb;"></td>
            </tr>
            <tr>
                <td colspan="6" class="text-right font-bold">RATA-RATA NILAI AKADEMIK:</td>
                <td class="text-center font-bold" style="background-color: #f3f4f6;">
                    {{ number_format($computedRata, 2) }}
                </td>
                <td class="text-center font-bold" style="background-color: #f3f4f6;">
                    {{ \App\Models\Nilai::calculateGrade($computedRata) }}
                </td>
                <td style="background-color: #f3f4f6;"></td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- B. Penilaian Sikap / Kepesantrenan & C. Rekap Kehadiran --}}
    <table class="eval-grid">
        <tr>
            <td class="panel" width="54%" style="padding-right: 10px;">
                <div style="font-weight: bold; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">
                    B. Penilaian Sikap / Kepesantrenan &amp; Catatan Wali Kelas
                </div>
                <table class="sub-table">
                    <tr>
                        <th width="42%">Predikat Sikap &amp; Adab</th>
                        <td><strong>{{ ($nilaiSikap ?? $santri->nilai_sikap) ?: 'Baik (B)' }}</strong></td>
                    </tr>
                    <tr>
                        <th colspan="2">Catatan Evaluasi Wali Kelas</th>
                    </tr>
                    <tr>
                        <td colspan="2" style="min-height: 48px; line-height: 1.45;">
                            {{ ($catatanWaliKelas ?? $santri->catatan_wali_kelas) ?: 'Tingkatkan terus semangat belajar, kedisiplinan jamaah, serta akhlaqul karimah di lingkungan pesantren maupun di rumah.' }}
                        </td>
                    </tr>
                </table>
            </td>
            <td class="panel" width="46%" style="padding-left: 10px;">
                <div style="font-weight: bold; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">
                    C. Rekapitulasi Presensi Kehadiran
                </div>
                <table class="sub-table">
                    <tr>
                        <th>Status Kehadiran</th>
                        <th class="text-center" width="35%">Jumlah</th>
                    </tr>
                    <tr>
                        <td>Hadir (H)</td>
                        <td class="text-center font-bold">{{ $totalHadir }} Sesi</td>
                    </tr>
                    <tr>
                        <td>Sakit (S)</td>
                        <td class="text-center">{{ $totalSakit }} Sesi</td>
                    </tr>
                    <tr>
                        <td>Izin (I)</td>
                        <td class="text-center">{{ $totalIzin }} Sesi</td>
                    </tr>
                    <tr>
                        <td>Tanpa Keterangan / Alpa (A)</td>
                        <td class="text-center">{{ $totalAlpa }} Sesi</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- 3 Blok Tanda Tangan Resmi Tervalidasi Kepala Madrasah (FR-12) --}}
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td width="33%">
                    Mengetahui,<br>
                    Orang Tua / Wali Santri,
                    <div class="signature-space"></div>
                    <strong>( {{ $santri->nama_wali ?: '........................................' }} )</strong>
                </td>
                <td width="33%">
                    <br>
                    Wali Kelas,
                    <div class="signature-space"></div>
                    <strong><u>{{ $kelasRapor->waliKelas->nama_lengkap ?? '........................................' }}</u></strong><br>
                    NIY. {{ $kelasRapor->waliKelas->nomor_identitas ?? '-' }}
                </td>
                <td width="34%">
                    Lamongan, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    Pimpinan Pondok / Kepala Sekolah,
                    <div class="signature-space"></div>
                    <strong><u>{{ $kepalaMadrasah }}</u></strong><br>
                    Pondok Pesantren Nurul Khidmah
                </td>
            </tr>
        </table>
    </div>
@endsection
