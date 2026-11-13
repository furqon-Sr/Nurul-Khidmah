<?php

namespace App\Livewire\Admin;

use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Santri;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RaporSantriSearch extends Component
{
    public string $search = '';

    public ?int $tahunAjaranId = null;

    public string $tingkat = '';

    public ?int $filterKelasId = null;

    public bool $hasYearFilter = false;

    public ?int $selectedSantriId = null;

    public function mount(
        ?int $tahunAjaranId = null,
        string $tingkat = '',
        ?int $filterKelasId = null,
        bool $hasYearFilter = false,
        string $initialSearch = ''
    ): void {
        $this->tahunAjaranId = $tahunAjaranId;
        $this->tingkat = trim($tingkat);
        $this->filterKelasId = $filterKelasId;
        $this->hasYearFilter = $hasYearFilter;
        $this->search = trim($initialSearch);

        $first = $this->matchingResults()->first();
        $this->selectedSantriId = $first['id'] ?? null;
    }

    public function updatedSearch(): void
    {
        $results = $this->matchingResults();
        if (! $results->contains('id', $this->selectedSantriId)) {
            $this->selectedSantriId = $results->first()['id'] ?? null;
        }
    }

    public function selectSantri(int $santriId): void
    {
        if ($this->matchingResults()->contains('id', $santriId)) {
            $this->selectedSantriId = $santriId;
        }
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->updatedSearch();
    }

    private function matchingResults(): Collection
    {
        $user = Auth::user();
        $allowedClassIds = collect();

        $classesQuery = Kelas::query()->orderBy('tingkat_kelas')->orderBy('nama_kelas');
        if ($user && $user->isTenagaPengajar()) {
            $pengajarId = $user->pengajar?->id ?? 0;
            $waliClassIds = Kelas::where('wali_kelas_id', $pengajarId)->pluck('id');
            if ($waliClassIds->isNotEmpty()) {
                $classesQuery->whereIn('id', $waliClassIds);
            } else {
                $taughtClassIds = JadwalPelajaran::where('pengajar_id', $pengajarId)->pluck('kelas_id')->unique();
                $classesQuery->whereIn('id', $taughtClassIds);
            }
        }

        $classes = $classesQuery->get(['id', 'tahun_ajaran_id', 'tingkat_kelas', 'nama_kelas']);
        $allowedClassIds = $classes->pluck('id');

        $filteredClasses = $classes->filter(function (Kelas $c) {
            if ($this->tahunAjaranId && $c->tahun_ajaran_id && (int) $c->tahun_ajaran_id !== (int) $this->tahunAjaranId) {
                return false;
            }
            if ($this->tingkat !== '') {
                $haystack = strtolower(($c->tingkat_kelas ?? '').' '.($c->nama_kelas ?? ''));
                if (! str_contains($haystack, strtolower($this->tingkat))) {
                    return false;
                }
            }

            return true;
        })->values();

        if (! $this->hasYearFilter && $this->tingkat === '' && $filteredClasses->isEmpty() && $classes->isNotEmpty()) {
            $filteredClasses = $classes;
        }

        $selectedFilterKelas = $this->filterKelasId
            ? $classes->firstWhere('id', (int) $this->filterKelasId)
            : null;

        $santrisQuery = Santri::with([
            'kelas:id,nama_kelas',
            'raporSemesters' => fn ($q) => $q->when(
                $this->tahunAjaranId,
                fn ($rq) => $rq->where('tahun_ajaran_id', $this->tahunAjaranId)
            ),
        ])
            ->whereIn('status_santri', ['Aktif', 'Lulus'])
            ->orderBy('nama_lengkap');

        if ($user && $user->isTenagaPengajar()) {
            $santrisQuery->where(function ($q) use ($allowedClassIds) {
                $q->whereIn('kelas_id', $allowedClassIds)
                    ->orWhereHas('riwayatKelas', fn ($rq) => $rq->whereIn('kelas_baru_id', $allowedClassIds)->orWhereIn('kelas_lama_id', $allowedClassIds));
            });
        }

        if ($selectedFilterKelas) {
            $kId = $selectedFilterKelas->id;
            $taId = $this->tahunAjaranId;
            $santrisQuery->where(function ($q) use ($kId, $taId) {
                $q->where('kelas_id', $kId)
                    ->orWhereHas('riwayatKelas', function ($rq) use ($kId, $taId) {
                        $rq->where('kelas_baru_id', $kId);
                        if ($taId) {
                            $rq->where('tahun_ajaran_id', $taId);
                        }
                    })
                    ->orWhereHas('riwayatKelas', function ($rq) use ($kId, $taId) {
                        $rq->where('kelas_lama_id', $kId);
                        if ($taId) {
                            $rq->whereHas('kelasLama', fn ($kq) => $kq->where('tahun_ajaran_id', $taId));
                        }
                    });
            });
        } elseif ($this->tingkat !== '') {
            $matchingClassIds = $filteredClasses->pluck('id');
            $taId = $this->tahunAjaranId;
            $santrisQuery->where(function ($q) use ($matchingClassIds, $taId) {
                $q->whereIn('kelas_id', $matchingClassIds)
                    ->orWhereHas('riwayatKelas', function ($rq) use ($matchingClassIds, $taId) {
                        $rq->whereIn('kelas_baru_id', $matchingClassIds);
                        if ($taId) {
                            $rq->where('tahun_ajaran_id', $taId);
                        }
                    });
            });
        } elseif ($this->tahunAjaranId && $this->hasYearFilter) {
            $yearClassIds = $classes->where('tahun_ajaran_id', $this->tahunAjaranId)->pluck('id');
            $taId = $this->tahunAjaranId;
            $santrisQuery->where(function ($q) use ($yearClassIds, $taId) {
                $q->whereIn('kelas_id', $yearClassIds)
                    ->orWhereHas('riwayatKelas', fn ($rq) => $rq->where('tahun_ajaran_id', $taId))
                    ->orWhereHas('riwayatKelas.kelasLama', fn ($kq) => $kq->where('tahun_ajaran_id', $taId))
                    ->orWhereHas('nilais.jadwalPelajaran', fn ($nq) => $nq->where('tahun_ajaran_id', $taId));
            });
        }

        $term = trim($this->search);
        if ($term !== '') {
            $santrisQuery->where(function ($q) use ($term) {
                $q->where('nama_lengkap', 'like', "%{$term}%")
                    ->orWhere('nis', 'like', "%{$term}%")
                    ->orWhere('nisn', 'like', "%{$term}%")
                    ->orWhereHas('kelas', fn ($kq) => $kq->where('nama_kelas', 'like', "%{$term}%"));
            });
        }

        return $santrisQuery->get()->map(function (Santri $santri) {
            $rapor = $this->tahunAjaranId
                ? $santri->raporSemesters->firstWhere('tahun_ajaran_id', $this->tahunAjaranId)
                : $santri->raporSemesters->sortByDesc('id')->first();

            $statusLabel = match (true) {
                $rapor?->status_publikasi === 'published' => 'Terbit',
                $rapor?->status_publikasi === 'approved' => 'Disetujui',
                $rapor?->workflow_stage === 'revision' => 'Revisi',
                $rapor?->workflow_stage === 'validated' => 'Siap Disetujui',
                default => 'Draft',
            };

            $routeParams = array_filter([
                'santri' => $santri->id,
                'tahun_ajaran_id' => $this->tahunAjaranId,
            ]);

            return [
                'id' => $santri->id,
                'nama_lengkap' => $santri->nama_lengkap,
                'nis' => $santri->nis,
                'nisn' => $santri->nisn,
                'kelas_nama' => $santri->kelas?->nama_kelas ?? ($santri->previous_school ?: 'Mondok Saja'),
                'status_label' => $statusLabel,
                'preview_url' => route('admin.reports.santri-rapor-pdf', $routeParams),
                'download_url' => route('admin.reports.santri-rapor.download', $routeParams),
            ];
        });
    }

    public function render()
    {
        $results = $this->matchingResults();
        $selectedItem = $results->firstWhere('id', $this->selectedSantriId) ?? $results->first();

        return view('livewire.admin.rapor-santri-search', [
            'results' => $results,
            'selectedItem' => $selectedItem,
        ]);
    }
}
