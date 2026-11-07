<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\RaporSemester;
use App\Models\RiwayatKelasSantri;
use App\Models\Santri;
use App\Models\TahunAjaran;
use Illuminate\Support\Collection;

class AcademicRaporService
{
    /**
     * Ambil seluruh periode Tahun Ajaran yang pernah diikuti santri (aktif + arsip kenaikan kelas).
     */
    public function resolveAvailableYearsForSantri(Santri $santri): Collection
    {
        $activeYear = TahunAjaran::getAktif();

        $historyRecords = RiwayatKelasSantri::where('santri_id', $santri->id)->get(['tahun_ajaran_id', 'kelas_lama_id', 'kelas_baru_id']);
        $historyYearIds = $historyRecords->pluck('tahun_ajaran_id')->filter();
        $historyKelasIds = $historyRecords->pluck('kelas_lama_id')
            ->merge($historyRecords->pluck('kelas_baru_id'))
            ->filter()
            ->unique();
        $kelasHistoryYearIds = $historyKelasIds->isNotEmpty()
            ? Kelas::whereIn('id', $historyKelasIds)->whereNotNull('tahun_ajaran_id')->pluck('tahun_ajaran_id')
            : collect();

        $nilaiYearIds = JadwalPelajaran::whereIn(
            'id',
            Nilai::where('santri_id', $santri->id)->select('jadwal_pelajaran_id')
        )->pluck('tahun_ajaran_id');

        $absensiYearIds = JadwalPelajaran::whereIn(
            'id',
            Absensi::where('santri_id', $santri->id)->select('jadwal_pelajaran_id')
        )->pluck('tahun_ajaran_id');

        $yearIds = collect([$activeYear?->id, $santri->kelas?->tahun_ajaran_id])
            ->merge($historyYearIds)
            ->merge($kelasHistoryYearIds)
            ->merge($nilaiYearIds)
            ->merge($absensiYearIds)
            ->filter()
            ->unique()
            ->values();

        if ($yearIds->isEmpty()) {
            return TahunAjaran::orderByDesc('status_aktif')->orderByDesc('id')->get();
        }

        return TahunAjaran::whereIn('id', $yearIds)
            ->orderByDesc('status_aktif')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Tentukan kelas santri pada periode Tahun Ajaran tertentu (mendukung arsip Kelas 7/8/9 setelah naik kelas).
     */
    public function resolveKelasForSantriInYear(Santri $santri, ?TahunAjaran $tahunAjaran): ?Kelas
    {
        $santri->loadMissing(['kelas.waliKelas', 'kelas.tahunAjaran', 'kelas.educationalUnit.headmaster']);

        if (! $tahunAjaran) {
            return $santri->kelas;
        }

        if ($santri->kelas && (int) $santri->kelas->tahun_ajaran_id === (int) $tahunAjaran->id) {
            return $santri->kelas;
        }

        // Cek dari riwayat kelas pada tahun ajaran tersebut
        $history = RiwayatKelasSantri::with(['kelasBaru.waliKelas', 'kelasBaru.tahunAjaran', 'kelasBaru.educationalUnit.headmaster', 'kelasLama.waliKelas', 'kelasLama.educationalUnit.headmaster'])
            ->where('santri_id', $santri->id)
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->whereNotNull('kelas_baru_id')
            ->latest('id')
            ->first();

        if ($history && $history->kelasBaru) {
            return $history->kelasBaru;
        }

        $oldClassHistory = RiwayatKelasSantri::with(['kelasLama.waliKelas', 'kelasLama.tahunAjaran', 'kelasLama.educationalUnit.headmaster'])
            ->where('santri_id', $santri->id)
            ->whereHas('kelasLama', fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->latest('id')
            ->first();

        if ($oldClassHistory && $oldClassHistory->kelasLama) {
            return $oldClassHistory->kelasLama;
        }

        // Cek dari jejak jadwal nilai atau presensi pada tahun ajaran tersebut
        $nilaiOnYear = Nilai::with(['jadwalPelajaran.kelas.waliKelas', 'jadwalPelajaran.kelas.tahunAjaran', 'jadwalPelajaran.kelas.educationalUnit.headmaster'])
            ->where('santri_id', $santri->id)
            ->whereHas('jadwalPelajaran', fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->first();

        if ($nilaiOnYear?->jadwalPelajaran?->kelas) {
            return $nilaiOnYear->jadwalPelajaran->kelas;
        }

        $absensiOnYear = Absensi::with(['jadwalPelajaran.kelas.waliKelas', 'jadwalPelajaran.kelas.tahunAjaran', 'jadwalPelajaran.kelas.educationalUnit.headmaster'])
            ->where('santri_id', $santri->id)
            ->whereHas('jadwalPelajaran', fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->first();

        if ($absensiOnYear?->jadwalPelajaran?->kelas) {
            return $absensiOnYear->jadwalPelajaran->kelas;
        }

        return $santri->kelas;
    }

    /**
     * Susun data lengkap Rapor Santri beserta rincian spesifik seluruh Mata Pelajaran di kelasnya.
     */
    public function buildSantriRaporData(Santri $santri, ?TahunAjaran $tahunAjaran): array
    {
        $santri->loadMissing([
            'kelas.waliKelas',
            'kelas.tahunAjaran',
            'kelas.educationalUnit.headmaster',
            'raporSemesters',
        ]);

        $kelasPeriode = $this->resolveKelasForSantriInYear($santri, $tahunAjaran);

        $schedules = collect();
        if ($kelasPeriode) {
            $schedules = JadwalPelajaran::with(['mataPelajaran', 'pengajar'])
                ->where('kelas_id', $kelasPeriode->id)
                ->when($tahunAjaran, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
                ->get();
        }

        $nilais = Nilai::with([
            'jadwalPelajaran.mataPelajaran',
            'jadwalPelajaran.pengajar',
            'mataPelajaran',
            'gradeComponent',
        ])
            ->where('santri_id', $santri->id)
            ->when($tahunAjaran, function ($q) use ($tahunAjaran) {
                $q->whereHas('jadwalPelajaran', fn ($jq) => $jq->where('tahun_ajaran_id', $tahunAjaran->id));
            })
            ->get();

        $nilaiByMapel = $nilais->groupBy(fn ($n) => $n->jadwalPelajaran?->mata_pelajaran_id ?? ('jadwal-'.$n->jadwal_pelajaran_id));
        $handledMapelIds = [];
        $subjectRows = collect();

        foreach ($schedules->groupBy('mata_pelajaran_id') as $mapelId => $mapelSchedules) {
            $firstSched = $mapelSchedules->first();
            $handledMapelIds[] = $mapelId;

            $matchedNilais = $nilaiByMapel->get($mapelId, collect());
            $chosenNilai = $matchedNilais->sortByDesc('updated_at')->first();

            $subjectRows->push((object) [
                'mata_pelajaran_id' => $mapelId,
                'kode_mapel' => $firstSched->mataPelajaran->kode_mapel ?? '-',
                'nama_mapel' => $firstSched->mataPelajaran->nama_mapel ?? '-',
                'pengajar_nama' => $chosenNilai?->jadwalPelajaran?->pengajar?->nama_lengkap
                    ?? $firstSched->pengajar?->nama_lengkap
                    ?? '-',
                'has_nilai' => $chosenNilai !== null,
                'nilai_tugas' => $chosenNilai?->nilai_tugas,
                'nilai_uts' => $chosenNilai?->nilai_uts,
                'nilai_uas' => $chosenNilai?->nilai_uas,
                'nilai_akhir' => $chosenNilai?->nilai_akhir,
                'grade' => $chosenNilai?->grade,
                'keterangan' => $chosenNilai?->keterangan,
            ]);
        }

        // Tambahkan nilai lain pada tahun ajaran yang sama bila jadwalnya berada di luar grup di atas
        foreach ($nilais as $extraNilai) {
            $mId = $extraNilai->jadwalPelajaran?->mata_pelajaran_id;
            if ($mId && in_array($mId, $handledMapelIds, true)) {
                continue;
            }
            if ($mId) {
                $handledMapelIds[] = $mId;
            }

            $subjectRows->push((object) [
                'mata_pelajaran_id' => $mId,
                'kode_mapel' => $extraNilai->jadwalPelajaran?->mataPelajaran?->kode_mapel ?? '-',
                'nama_mapel' => $extraNilai->jadwalPelajaran?->mataPelajaran?->nama_mapel ?? '-',
                'pengajar_nama' => $extraNilai->jadwalPelajaran?->pengajar?->nama_lengkap ?? '-',
                'has_nilai' => true,
                'nilai_tugas' => $extraNilai->nilai_tugas,
                'nilai_uts' => $extraNilai->nilai_uts,
                'nilai_uas' => $extraNilai->nilai_uas,
                'nilai_akhir' => $extraNilai->nilai_akhir,
                'grade' => $extraNilai->grade,
                'keterangan' => $extraNilai->keterangan,
            ]);
        }

        $subjectRows = $subjectRows->sortBy('nama_mapel')->values();
        $gradedRows = $subjectRows->where('has_nilai', true);
        $jumlahNilai = $gradedRows->isNotEmpty() ? round((float) $gradedRows->sum('nilai_akhir'), 2) : 0.0;
        $rataRata = $gradedRows->isNotEmpty() ? round((float) $gradedRows->avg('nilai_akhir'), 2) : 0.0;

        $absensiRecords = Absensi::where('santri_id', $santri->id)
            ->when($tahunAjaran, function ($q) use ($tahunAjaran) {
                $q->whereHas('jadwalPelajaran', fn ($jq) => $jq->where('tahun_ajaran_id', $tahunAjaran->id));
            })
            ->get();

        // Ambil catatan wali kelas & nilai sikap dari tabel rapor_semesters sesuai tahun ajaran & santri aktif
        $targetYearId = $tahunAjaran?->id ?? $kelasPeriode?->tahun_ajaran_id ?? TahunAjaran::getAktif()?->id;
        $raporSemester = null;
        if ($targetYearId) {
            if ($santri->relationLoaded('raporSemesters')) {
                $raporSemester = $santri->raporSemesters->firstWhere('tahun_ajaran_id', $targetYearId);
                $raporSemester?->loadMissing(['approvedBy', 'publishedBy']);
            }
            if (! $raporSemester) {
                $raporSemester = RaporSemester::with(['approvedBy', 'publishedBy'])
                    ->where('santri_id', $santri->id)
                    ->where('tahun_ajaran_id', $targetYearId)
                    ->first();
            }
        }

        $catatanWaliKelas = $raporSemester?->catatan_wali_kelas;
        $nilaiSikap = $raporSemester?->nilai_sikap;
        $statusKenaikan = $raporSemester?->status_kenaikan ?? 'Aktif';
        $statusPublikasi = $raporSemester?->status_publikasi ?? 'draft';

        return [
            'kelasPeriode' => $kelasPeriode,
            'subjectRows' => $subjectRows,
            'nilais' => $nilais,
            'gradedCount' => $gradedRows->count(),
            'totalMapelCount' => $subjectRows->count(),
            'jumlahNilai' => $jumlahNilai,
            'rataRata' => $rataRata,
            'totalHadir' => $absensiRecords->where('status_kehadiran', 'Hadir')->count(),
            'totalIzin' => $absensiRecords->where('status_kehadiran', 'Izin')->count(),
            'totalSakit' => $absensiRecords->where('status_kehadiran', 'Sakit')->count(),
            'totalAlpa' => $absensiRecords->where('status_kehadiran', 'Alpa')->count(),
            'totalTerlambat' => $absensiRecords->where('status_kehadiran', 'Terlambat')->count(),
            'totalBolos' => $absensiRecords->where('status_kehadiran', 'Bolos')->count(),
            'raporSemester' => $raporSemester,
            'catatanWaliKelas' => $catatanWaliKelas,
            'nilaiSikap' => $nilaiSikap,
            'statusKenaikan' => $statusKenaikan,
            'statusPublikasi' => $statusPublikasi,
        ];
    }
}
