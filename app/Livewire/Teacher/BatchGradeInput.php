<?php

namespace App\Livewire\Teacher;

use App\Models\GradeComponent;
use App\Models\GradePredicate;
use App\Models\JadwalPelajaran;
use App\Models\Nilai;
use App\Models\RaporSemester;
use App\Models\Santri;
use App\Models\TahunAjaran;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BatchGradeInput extends Component
{
    use AuthorizesRequests;

    public JadwalPelajaran $jadwal;

    public array $grades = [];

    public array $lockedSantriIds = [];

    public array $lockedSantris = [];

    public ?int $unitId = null;

    public float $bobotTugas = 30;

    public float $bobotUts = 30;

    public float $bobotUas = 40;

    public array $predList = [];

    public bool $hasSaved = false;

    public string $feedbackMessage = '';

    public function mount($jadwal = null, $schedule = null)
    {
        $targetJadwal = $jadwal ?? $schedule;
        if (is_numeric($targetJadwal)) {
            $targetJadwal = JadwalPelajaran::findOrFail($targetJadwal);
        }

        $this->jadwal = $targetJadwal;

        $user = Auth::user();
        if ($user && $user->isTenagaPengajar() && (int) $this->jadwal->pengajar_id !== (int) ($user->pengajar?->id ?? 0)) {
            abort(403, 'Anda hanya dapat mengolah nilai pada jadwal mengajar Anda sendiri.');
        }

        // Muat daftar santri menggunakan Eager Loading yang dioptimasi di Fase 6.1
        $this->jadwal->loadMissing([
            'kelas.educationalUnit',
            'mataPelajaran.educationalUnit',
            'pengajar',
            'tahunAjaran',
            'santris' => fn ($q) => $q->where('status_santri', 'Aktif')->orderBy('nama_lengkap'),
            'santris.raporSemesters',
            'nilais',
        ]);

        $this->unitId = $this->jadwal->kelas?->educational_unit_id ?? $this->jadwal->mataPelajaran?->educational_unit_id;
        $components = GradeComponent::getComponentsForUnit($this->unitId);
        $predicates = GradePredicate::getPredicatesForUnit($this->unitId);

        $this->bobotTugas = (float) ($components->firstWhere(fn ($c) => strtolower($c->nama_komponen) === 'tugas')?->bobot_persen ?? 30);
        $this->bobotUts = (float) ($components->firstWhere(fn ($c) => strtolower($c->nama_komponen) === 'uts')?->bobot_persen ?? 30);
        $this->bobotUas = (float) ($components->firstWhere(fn ($c) => strtolower($c->nama_komponen) === 'uas')?->bobot_persen ?? 40);

        $this->predList = $predicates->isNotEmpty()
            ? $predicates->map(fn ($p) => [
                'grade' => $p->huruf,
                'min' => (float) $p->batas_bawah,
                'max' => (float) ($p->batas_atas ?? 100),
            ])->values()->toArray()
            : [
                ['grade' => 'A', 'min' => 85, 'max' => 100],
                ['grade' => 'B', 'min' => 75, 'max' => 84.99],
                ['grade' => 'C', 'min' => 65, 'max' => 74.99],
                ['grade' => 'D', 'min' => 50, 'max' => 64.99],
                ['grade' => 'E', 'min' => 0, 'max' => 49.99],
            ];

        $targetTaId = $this->jadwal->tahun_ajaran_id ?? TahunAjaran::getAktif()?->id;
        $existingNilai = $this->jadwal->nilais->keyBy('santri_id');

        $this->grades = [];
        $this->lockedSantriIds = [];
        $this->lockedSantris = [];

        foreach ($this->jadwal->santris as $santri) {
            $rapor = $santri->raporSemesters->firstWhere('tahun_ajaran_id', $targetTaId);
            $isApproved = $rapor && in_array($rapor->status_publikasi, ['approved', 'published'], true);
            $currentNilai = $existingNilai->get($santri->id);

            // Pengecualian santri yang status rapornya sudah approved
            if ($isApproved) {
                $this->lockedSantriIds[] = $santri->id;
                $this->lockedSantris[$santri->id] = [
                    'santri_id' => $santri->id,
                    'nama_lengkap' => $santri->nama_lengkap,
                    'nis' => $santri->nis,
                    'nilai_tugas' => $currentNilai ? $currentNilai->nilai_tugas : '-',
                    'nilai_uts' => $currentNilai ? $currentNilai->nilai_uts : '-',
                    'nilai_uas' => $currentNilai ? $currentNilai->nilai_uas : '-',
                    'nilai_akhir' => $currentNilai ? $currentNilai->nilai_akhir : '-',
                    'grade' => $currentNilai ? $currentNilai->grade : '-',
                    'keterangan' => $currentNilai ? ($currentNilai->keterangan ?? '-') : '-',
                ];

                continue;
            }

            $this->grades[$santri->id] = [
                'santri_id' => $santri->id,
                'nama_lengkap' => $santri->nama_lengkap,
                'nis' => $santri->nis,
                'nilai_tugas' => $currentNilai ? $currentNilai->nilai_tugas : '',
                'nilai_uts' => $currentNilai ? $currentNilai->nilai_uts : '',
                'nilai_uas' => $currentNilai ? $currentNilai->nilai_uas : '',
                'nilai_akhir' => $currentNilai ? $currentNilai->nilai_akhir : '',
                'grade' => $currentNilai ? $currentNilai->grade : '-',
                'keterangan' => $currentNilai ? ($currentNilai->keterangan ?? '') : '',
            ];
        }
    }

    public function rules(): array
    {
        return [
            'grades.*.nilai_tugas' => 'nullable|numeric|min:0|max:100',
            'grades.*.nilai_uts' => 'nullable|numeric|min:0|max:100',
            'grades.*.nilai_uas' => 'nullable|numeric|min:0|max:100',
            'grades.*.keterangan' => 'nullable|string|max:500',
        ];
    }

    public function save()
    {
        $this->validate();

        $totalBobot = ($this->bobotTugas + $this->bobotUts + $this->bobotUas) ?: 100;
        $now = now();
        $authId = Auth::id();
        $targetTaId = $this->jadwal->tahun_ajaran_id ?? TahunAjaran::getAktif()?->id;

        // Eager load rapors for authorization without N+1 query leaks
        $santriIds = array_map('intval', array_keys($this->grades));
        $rapors = RaporSemester::whereIn('santri_id', $santriIds)
            ->where('tahun_ajaran_id', $targetTaId)
            ->get()
            ->keyBy('santri_id');

        $upsertData = [];

        foreach ($this->grades as $santriId => $row) {
            $rapor = $rapors->get((int) $santriId);
            $dummySantri = new Santri(['id' => (int) $santriId]);
            $dummySantri->setRelation('raporSemesters', collect($rapor ? [$rapor] : []));

            // Validasi otorisasi Policy (Fase 6.2)
            $dummyNilai = new Nilai([
                'jadwal_pelajaran_id' => $this->jadwal->id,
                'santri_id' => (int) $santriId,
            ]);
            $dummyNilai->setRelation('jadwalPelajaran', $this->jadwal);
            $dummyNilai->setRelation('santri', $dummySantri);

            $this->authorize('update', $dummyNilai);

            $hasAnyScore = ($row['nilai_tugas'] ?? '') !== '' || ($row['nilai_uts'] ?? '') !== '' || ($row['nilai_uas'] ?? '') !== '';
            $hasNote = filled($row['keterangan'] ?? null);

            if (! $hasAnyScore && ! $hasNote) {
                continue;
            }

            $tugas = floatval($row['nilai_tugas'] ?? 0);
            $uts = floatval($row['nilai_uts'] ?? 0);
            $uas = floatval($row['nilai_uas'] ?? 0);

            $nilaiAkhir = round((($tugas * $this->bobotTugas) + ($uts * $this->bobotUts) + ($uas * $this->bobotUas)) / $totalBobot, 2);
            $grade = Nilai::calculateGrade($nilaiAkhir, $this->unitId);

            $this->grades[$santriId]['nilai_akhir'] = $nilaiAkhir;
            $this->grades[$santriId]['grade'] = $grade;

            $upsertData[] = [
                'jadwal_pelajaran_id' => $this->jadwal->id,
                'santri_id' => (int) $santriId,
                'nilai_tugas' => $tugas,
                'nilai_uts' => $uts,
                'nilai_uas' => $uas,
                'nilai_akhir' => $nilaiAkhir,
                'grade' => $grade,
                'keterangan' => $hasNote ? trim($row['keterangan']) : null,
                'created_by' => $authId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($upsertData)) {
            Nilai::upsert(
                $upsertData,
                ['santri_id', 'jadwal_pelajaran_id'],
                ['nilai_tugas', 'nilai_uts', 'nilai_uas', 'nilai_akhir', 'grade', 'keterangan', 'created_by', 'updated_at']
            );
        }

        $this->hasSaved = true;
        $this->feedbackMessage = count($upsertData).' nilai santri berhasil disimpan sekaligus (batch upsert)!';

        session()->flash('success', $this->feedbackMessage);
    }

    public function render()
    {
        $this->jadwal->loadMissing(['kelas', 'mataPelajaran', 'pengajar', 'tahunAjaran']);

        return view('livewire.teacher.batch-grade-input');
    }
}
