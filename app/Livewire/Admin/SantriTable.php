<?php

namespace App\Livewire\Admin;

use App\Models\Kelas;
use App\Models\Santri;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SantriTable extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $kelas_id = '';

    #[Url(history: true)]
    public string $status = '';

    public function mount(?string $search = null, ?string $kelas_id = null, ?string $status = null): void
    {
        if ($search !== null && $this->search === '') {
            $this->search = $search;
        }
        if ($kelas_id !== null && $this->kelas_id === '') {
            $this->kelas_id = $kelas_id;
        }
        if ($status !== null && $this->status === '') {
            $this->status = $status;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingKelasId(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'kelas_id', 'status']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Santri::with(['user', 'kelas.tahunAjaran', 'registration', 'educationalUnit']);

        if (!empty($this->search)) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nama_wali', 'like', "%{$search}%")
                    ->orWhere('no_hp_wali', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('email', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($this->kelas_id)) {
            if ($this->kelas_id === 'unassigned') {
                $query->whereNull('kelas_id');
            } else {
                $query->where('kelas_id', $this->kelas_id);
            }
        }

        if (!empty($this->status)) {
            $status = strtolower($this->status);
            if (in_array($status, ['lulus', 'alumni'], true)) {
                $query->whereIn('status_santri', ['Lulus', 'Alumni']);
            } elseif (in_array($status, ['mutasi', 'non-aktif'], true)) {
                $query->whereIn('status_santri', ['Mutasi', 'Non-Aktif']);
            } elseif ($status === 'aktif') {
                $query->where('status_santri', 'Aktif');
            } else {
                $query->where('status_santri', $this->status);
            }
        }

        $santris = $query->orderBy('nama_lengkap', 'asc')->paginate(10);
        $kelasses = Kelas::with('tahunAjaran')->orderBy('nama_kelas')->get();

        return view('livewire.admin.santri-table', [
            'santris' => $santris,
            'kelasses' => $kelasses,
        ]);
    }
}
