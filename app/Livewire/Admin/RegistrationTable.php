<?php

namespace App\Livewire\Admin;

use App\Actions\AcceptSantriRegistration;
use App\Models\EducationProgram;
use App\Models\PageContent;
use App\Models\SantriRegistration;
use App\Models\TahunAjaran;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class RegistrationTable extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = '';

    #[Url(history: true)]
    public string $target_class = '';

    #[Url(history: true)]
    public string $academic_year = '';

    public array $selectedIds = [];
    public bool $selectAll = false;
    public string $bulkAction = '';

    public function mount(
        ?string $search = null,
        ?string $status = null,
        ?string $target_class = null,
        ?string $academic_year = null
    ): void {
        if ($search !== null && $this->search === '') {
            $this->search = $search;
        }
        if ($status !== null && $this->status === '') {
            $this->status = $status;
        }
        if ($target_class !== null && $this->target_class === '') {
            $this->target_class = $target_class;
        }
        if ($academic_year !== null && $this->academic_year === '') {
            $this->academic_year = $academic_year;
        }

        if (empty($this->academic_year)) {
            $this->academic_year = $this->getActiveYear();
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

    public function updatingTargetClass(): void
    {
        $this->resetPage();
    }

    public function updatingAcademicYear(): void
    {
        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        $this->status = $status;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'target_class']);
        $this->academic_year = $this->getActiveYear();
        $this->selectedIds = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedIds = $this->getFilteredQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function updateSingleStatus(int $id, string $newStatus, AcceptSantriRegistration $acceptAction): void
    {
        $reg = SantriRegistration::findOrFail($id);
        $oldStatus = $reg->status;
        $reg->update(['status' => $newStatus]);

        if (in_array($newStatus, ['accepted', 'approved'], true) && $oldStatus !== 'accepted') {
            $acceptAction->execute($reg);
        }

        session()->flash('success', "Status pendaftaran {$reg->student_name} berhasil diubah menjadi {$newStatus}.");
    }

    public function executeBulkStatus(AcceptSantriRegistration $acceptAction): void
    {
        if (empty($this->selectedIds) || empty($this->bulkAction)) {
            return;
        }

        $registrations = SantriRegistration::whereIn('id', $this->selectedIds)->get();
        foreach ($registrations as $reg) {
            $oldStatus = $reg->status;
            $reg->update(['status' => $this->bulkAction]);

            if (in_array($this->bulkAction, ['accepted', 'approved'], true) && $oldStatus !== 'accepted') {
                $acceptAction->execute($reg);
            }
        }

        $count = count($this->selectedIds);
        $this->selectedIds = [];
        $this->selectAll = false;
        $this->bulkAction = '';

        session()->flash('success', "Berhasil memperbarui status {$count} calon santri.");
    }

    protected function getActiveYear(): string
    {
        return PageContent::where('section_key', 'active_academic_year')->value('content_text')
            ?: TahunAjaran::getAktif()?->nama_tahun_ajaran
            ?: (date('Y') . '/' . (date('Y') + 1));
    }

    protected function getFilteredQuery()
    {
        $query = SantriRegistration::with('santri');
        $activeYear = $this->getActiveYear();
        $selectedYear = $this->academic_year ?: $activeYear;

        if ($selectedYear !== 'all') {
            $query->where(function ($q) use ($selectedYear, $activeYear) {
                $q->where('academic_year', $selectedYear);
                if ($selectedYear === $activeYear) {
                    $q->orWhereNull('academic_year');
                }
            });
        }

        if (!empty($this->search)) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('guardian_name', 'like', "%{$search}%");
            });
        }

        if (!empty($this->target_class)) {
            $query->where('target_class', $this->target_class);
        }

        if (!empty($this->status)) {
            $status = strtolower($this->status);
            if (in_array($status, ['accepted', 'approved'], true)) {
                $query->whereIn('status', ['accepted', 'approved']);
            } elseif ($status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($status === 'rejected') {
                $query->where('status', 'rejected');
            } else {
                $query->where('status', $this->status);
            }
        }

        return $query;
    }

    public function render()
    {
        $activeYear = $this->getActiveYear();
        $selectedYear = $this->academic_year ?: $activeYear;

        $academicYears = SantriRegistration::distinct()->pluck('academic_year')->filter()->toArray();
        if (!in_array($activeYear, $academicYears, true)) {
            $academicYears[] = $activeYear;
        }
        sort($academicYears);

        $classes = EducationProgram::active()->orderBy('order')->pluck('name');

        $statsQuery = SantriRegistration::query();
        if ($selectedYear !== 'all') {
            $statsQuery->where(function ($q) use ($selectedYear, $activeYear) {
                $q->where('academic_year', $selectedYear);
                if ($selectedYear === $activeYear) {
                    $q->orWhereNull('academic_year');
                }
            });
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'accepted' => (clone $statsQuery)->whereIn('status', ['accepted', 'approved'])->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
        ];

        $registrations = $this->getFilteredQuery()->latest()->paginate(15);

        return view('livewire.admin.registration-table', [
            'registrations' => $registrations,
            'classes' => $classes,
            'stats' => $stats,
            'academicYears' => $academicYears,
            'selectedYear' => $selectedYear,
            'activeYear' => $activeYear,
        ]);
    }
}
