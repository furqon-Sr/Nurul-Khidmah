<?php

namespace App\Livewire\Admin;

use App\Models\Pengajar;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TeacherTable extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = '';

    public function mount(?string $search = null, ?string $status = null): void
    {
        if ($search !== null && $this->search === '') {
            $this->search = $search;
        }
        if ($status !== null && $this->status === '') {
            $this->status = $status;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Pengajar::with('user');

        if (!empty($this->search)) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_identitas', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('email', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($this->status)) {
            $query->where('status_pengajar', $this->status);
        }

        $teachers = $query->orderBy('nama_lengkap', 'asc')->paginate(10);

        return view('livewire.admin.teacher-table', [
            'teachers' => $teachers,
        ]);
    }
}
