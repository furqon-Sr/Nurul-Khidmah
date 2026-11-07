<?php

namespace App\Livewire\Admin;

use App\Actions\AcceptSantriRegistration;
use App\Models\AdminActivityLog;
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

    #[Url(history: true)]
    public string $sortField = 'created_at';

    #[Url(history: true)]
    public string $sortDirection = 'desc';

    #[Url(history: true)]
    public int $perPage = 15;

    public array $selectedIds = [];

    public bool $selectAll = false;

    public string $bulkAction = '';

    /** @var array<int, string> */
    protected array $allowedSortFields = [
        'registration_number',
        'student_name',
        'created_at',
        'status',
    ];

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

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->allowedSortFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

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
        if (! in_array($newStatus, ['pending', 'accepted', 'rejected'], true)) {
            return;
        }

        $reg = SantriRegistration::findOrFail($id);
        $oldStatus = $reg->status;

        if ($newStatus === 'accepted') {
            if ($oldStatus !== 'accepted' || ! $reg->santri_id) {
                $acceptAction->execute($reg);
            } else {
                $reg->update(['status' => 'accepted']);
            }
        } else {
            $reg->update(['status' => $newStatus]);
        }

        AdminActivityLog::log(
            'update',
            "Mengubah status PPDB {$reg->student_name} ({$reg->registration_number}) menjadi ".strtoupper($newStatus),
            'SantriRegistration',
            $reg->id
        );

        $statusLabel = match ($newStatus) {
            'accepted' => 'Diterima (Approved)',
            'rejected' => 'Ditolak (Rejected)',
            default => 'Menunggu (Pending)',
        };

        session()->flash('livewire_success', "Status pendaftaran {$reg->student_name} berhasil diubah menjadi {$statusLabel}.");
    }

    public function deleteRegistration(int $id): void
    {
        $reg = SantriRegistration::findOrFail($id);
        $name = $reg->student_name;
        $regNo = $reg->registration_number;

        $reg->delete();

        $this->selectedIds = array_values(array_diff($this->selectedIds, [(string) $id]));

        AdminActivityLog::log(
            'delete',
            "Mengarsipkan (soft delete) data pendaftar PPDB: {$name} ({$regNo})",
            'SantriRegistration',
            $id
        );

        session()->flash('livewire_success', "Data pendaftar {$name} ({$regNo}) berhasil dihapus (diarsipkan).");
    }

    public function executeBulkStatus(AcceptSantriRegistration $acceptAction): void
    {
        if (empty($this->selectedIds) || empty($this->bulkAction)) {
            return;
        }

        if (! in_array($this->bulkAction, ['pending', 'accepted', 'rejected', 'delete'], true)) {
            return;
        }

        $registrations = SantriRegistration::whereIn('id', $this->selectedIds)->get();
        $count = $registrations->count();

        if ($this->bulkAction === 'delete') {
            foreach ($registrations as $reg) {
                $reg->delete();
            }

            AdminActivityLog::log(
                'delete',
                "Mengarsipkan (soft delete) massal {$count} data pendaftar PPDB",
                'SantriRegistration'
            );

            $this->selectedIds = [];
            $this->selectAll = false;
            $this->bulkAction = '';

            session()->flash('livewire_success', "Berhasil menghapus (mengarsipkan) {$count} data pendaftar.");

            return;
        }

        foreach ($registrations as $reg) {
            $oldStatus = $reg->status;

            if ($this->bulkAction === 'accepted') {
                if ($oldStatus !== 'accepted' || ! $reg->santri_id) {
                    $acceptAction->execute($reg);
                } else {
                    $reg->update(['status' => 'accepted']);
                }
            } else {
                $reg->update(['status' => $this->bulkAction]);
            }
        }

        AdminActivityLog::log(
            'update',
            "Mengubah status massal {$count} pendaftar PPDB menjadi ".strtoupper($this->bulkAction),
            'SantriRegistration'
        );

        $this->selectedIds = [];
        $this->selectAll = false;
        $this->bulkAction = '';

        session()->flash('livewire_success', "Berhasil memperbarui status {$count} calon santri.");
    }

    protected function getActiveYear(): string
    {
        return PageContent::where('section_key', 'active_academic_year')->value('content_text')
            ?: TahunAjaran::getAktif()?->nama_tahun_ajaran
            ?: (date('Y').'/'.(date('Y') + 1));
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

        if (! empty($this->search)) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('guardian_name', 'like', "%{$search}%");
            });
        }

        if (! empty($this->target_class)) {
            $query->where('target_class', $this->target_class);
        }

        if (! empty($this->status)) {
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
        if (! in_array($activeYear, $academicYears, true)) {
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

        $sortField = in_array($this->sortField, $this->allowedSortFields, true)
            ? $this->sortField
            : 'created_at';
        $sortDir = in_array(strtolower($this->sortDirection), ['asc', 'desc'], true)
            ? strtolower($this->sortDirection)
            : 'desc';
        $perPage = in_array($this->perPage, [10, 15, 25, 50], true)
            ? $this->perPage
            : 15;

        $registrations = $this->getFilteredQuery()
            ->orderBy($sortField, $sortDir)
            ->orderByDesc('id')
            ->paginate($perPage);

        $hasActiveFilters = ! empty($this->search)
            || ! empty($this->target_class)
            || ! empty($this->status)
            || ($selectedYear !== $activeYear);

        return view('livewire.admin.registration-table', [
            'registrations' => $registrations,
            'classes' => $classes,
            'stats' => $stats,
            'academicYears' => $academicYears,
            'selectedYear' => $selectedYear,
            'activeYear' => $activeYear,
            'hasActiveFilters' => $hasActiveFilters,
            'sortField' => $sortField,
            'sortDirection' => $sortDir,
        ]);
    }
}
