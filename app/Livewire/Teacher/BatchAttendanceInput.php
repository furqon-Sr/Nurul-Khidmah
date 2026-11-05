<?php

namespace App\Livewire\Teacher;

use App\Models\Absensi;
use App\Models\JadwalPelajaran;
use App\Models\RaporSemester;
use App\Models\Santri;
use App\Models\TahunAjaran;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BatchAttendanceInput extends Component
{
    use AuthorizesRequests;

    public JadwalPelajaran $jadwal;

    public string $tanggal = '';

    public array $attendances = [];

    public array $lockedSantriIds = [];

    public array $lockedSantris = [];

    public bool $hasSaved = false;

    public string $feedbackMessage = '';

    public function mount($jadwal = null, $tanggal = null, $schedule = null)
    {
        $targetJadwal = $jadwal ?? $schedule;
        if (is_numeric($targetJadwal)) {
            $targetJadwal = JadwalPelajaran::findOrFail($targetJadwal);
        }

        $this->jadwal = $targetJadwal;
        $this->tanggal = $tanggal ?: now()->toDateString();

        $user = Auth::user();
        if ($user && $user->isTenagaPengajar() && (int) $this->jadwal->pengajar_id !== (int) ($user->pengajar?->id ?? 0)) {
            abort(403, 'Anda hanya dapat mengolah presensi pada jadwal mengajar Anda sendiri.');
        }

        $this->loadAttendanceData();
    }

    public function updatedTanggal($newDate): void
    {
        if (empty($newDate)) {
            return;
        }

        $this->loadAttendanceData();
    }

    public function loadAttendanceData(): void
    {
        $this->jadwal->loadMissing([
            'kelas.educationalUnit',
            'mataPelajaran.educationalUnit',
            'pengajar',
            'tahunAjaran',
            'santris' => fn ($q) => $q->where('status_santri', 'Aktif')->orderBy('nama_lengkap'),
            'santris.raporSemesters',
        ]);

        $targetTaId = $this->jadwal->tahun_ajaran_id ?? TahunAjaran::getAktif()?->id;

        $existing = Absensi::where('jadwal_pelajaran_id', $this->jadwal->id)
            ->where('tanggal', $this->tanggal)
            ->get()
            ->keyBy('santri_id');

        $this->attendances = [];
        $this->lockedSantriIds = [];
        $this->lockedSantris = [];

        foreach ($this->jadwal->santris as $santri) {
            $rapor = $santri->raporSemesters->firstWhere('tahun_ajaran_id', $targetTaId);
            $isApproved = $rapor && in_array($rapor->status_publikasi, ['approved', 'published'], true);
            $current = $existing->get($santri->id);

            // Pengecualian santri yang status rapornya sudah approved
            if ($isApproved) {
                $this->lockedSantriIds[] = $santri->id;
                $this->lockedSantris[$santri->id] = [
                    'santri_id' => $santri->id,
                    'nama_lengkap' => $santri->nama_lengkap,
                    'nis' => $santri->nis,
                    'status' => $current ? $current->status_kehadiran : 'Hadir',
                    'keterangan' => $current ? ($current->keterangan ?? '-') : '-',
                ];

                continue;
            }

            $this->attendances[$santri->id] = [
                'santri_id' => $santri->id,
                'nama_lengkap' => $santri->nama_lengkap,
                'nis' => $santri->nis,
                'status' => $current ? $current->status_kehadiran : 'Hadir',
                'keterangan' => $current ? ($current->keterangan ?? '') : '',
            ];
        }
    }

    public function markAll(string $status = 'Hadir'): void
    {
        $validStatuses = ['Hadir', 'Izin', 'Sakit', 'Alpa', 'Terlambat', 'Bolos'];
        if (! in_array($status, $validStatuses, true)) {
            return;
        }

        foreach ($this->attendances as $santriId => $data) {
            $this->attendances[$santriId]['status'] = $status;
        }
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'required|date',
            'attendances.*.status' => 'required|in:Hadir,Izin,Sakit,Alpa,Terlambat,Bolos',
            'attendances.*.keterangan' => 'nullable|string|max:255',
        ];
    }

    public function save()
    {
        $this->validate();

        $targetTaId = $this->jadwal->tahun_ajaran_id ?? TahunAjaran::getAktif()?->id;
        $santriIds = array_map('intval', array_keys($this->attendances));

        // Eager load rapors for authorization check without N+1 query leaks
        $rapors = RaporSemester::whereIn('santri_id', $santriIds)
            ->where('tahun_ajaran_id', $targetTaId)
            ->get()
            ->keyBy('santri_id');

        $now = now();
        $authId = Auth::id();
        $upsertData = [];

        foreach ($this->attendances as $santriId => $row) {
            $rapor = $rapors->get((int) $santriId);
            $dummySantri = new Santri(['id' => (int) $santriId]);
            $dummySantri->setRelation('raporSemesters', collect($rapor ? [$rapor] : []));

            // Validasi otorisasi Policy (Fase 6.2)
            $dummyAbsensi = new Absensi([
                'jadwal_pelajaran_id' => $this->jadwal->id,
                'santri_id' => (int) $santriId,
                'tanggal' => $this->tanggal,
            ]);
            $dummyAbsensi->setRelation('jadwalPelajaran', $this->jadwal);
            $dummyAbsensi->setRelation('santri', $dummySantri);

            $this->authorize('update', $dummyAbsensi);

            $upsertData[] = [
                'santri_id' => (int) $santriId,
                'jadwal_pelajaran_id' => $this->jadwal->id,
                'tanggal' => $this->tanggal,
                'status_kehadiran' => $row['status'] ?? 'Hadir',
                'keterangan' => filled($row['keterangan'] ?? null) ? trim($row['keterangan']) : null,
                'created_by' => $authId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($upsertData)) {
            Absensi::upsert(
                $upsertData,
                ['santri_id', 'jadwal_pelajaran_id', 'tanggal'],
                ['status_kehadiran', 'keterangan', 'created_by', 'updated_at']
            );
        }

        $this->hasSaved = true;
        $this->feedbackMessage = count($upsertData).' presensi santri berhasil disimpan sekaligus (batch upsert)!';

        session()->flash('success', $this->feedbackMessage);
    }

    public function render()
    {
        $this->jadwal->loadMissing(['kelas', 'mataPelajaran', 'pengajar', 'tahunAjaran']);

        return view('livewire.teacher.batch-attendance-input');
    }
}
