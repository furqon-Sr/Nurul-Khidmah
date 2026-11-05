<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JadwalPelajaran;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeYear = TahunAjaran::getAktif();

        $query = JadwalPelajaran::with(['tahunAjaran', 'kelas', 'mataPelajaran', 'pengajar', 'santris']);

        if ($activeYear && ! $request->filled('semua_periode')) {
            $query->where('tahun_ajaran_id', $activeYear->id);
        }

        if ($user->isTenagaPengajar()) {
            $pengajarId = $user->pengajar?->id ?? 0;
            $query->where('pengajar_id', $pengajarId);
        }

        $schedules = $query->orderByHari()
            ->orderBy('waktu_mulai')
            ->get();

        return view('teacher.attendance.index', compact('schedules', 'activeYear'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:jadwal_pelajarans,id',
            'tanggal' => 'required|date',
        ]);

        $schedule = JadwalPelajaran::findOrFail($request->schedule_id);
        $this->authorizeScheduleAccess($schedule);
        $tanggal = $request->tanggal;

        return view('teacher.attendance.create', [
            'schedule' => $schedule,
            'jadwal' => $schedule,
            'tanggal' => $tanggal,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jadwal_pelajaran_id' => 'required|exists:jadwal_pelajarans,id',
            'tanggal' => 'required|date',
            'absensi' => 'required|array',
            'absensi.*.status' => 'required|in:Hadir,Izin,Sakit,Alpa,Terlambat,Bolos',
            'absensi.*.keterangan' => 'nullable|string|max:255',
        ]);

        $schedule = JadwalPelajaran::with(['tahunAjaran', 'absensis' => fn ($q) => $q->where('tanggal', $validated['tanggal'])])
            ->findOrFail($validated['jadwal_pelajaran_id']);
        $this->authorizeScheduleAccess($schedule);

        $tanggal = $validated['tanggal'];

        foreach ($validated['absensi'] as $santriId => $data) {
            $absensi = $schedule->absensis->firstWhere('santri_id', (int) $santriId)
                ?? Absensi::where('jadwal_pelajaran_id', $schedule->id)
                    ->where('santri_id', $santriId)
                    ->where('tanggal', $tanggal)
                    ->first()
                ?? new Absensi([
                    'jadwal_pelajaran_id' => $schedule->id,
                    'santri_id' => $santriId,
                    'tanggal' => $tanggal,
                ]);

            $absensi->setRelation('jadwalPelajaran', $schedule);

            // Validasi hak akses & status penguncian absensi via AbsensiPolicy
            $this->authorize('update', $absensi);

            Absensi::updateOrCreate(
                [
                    'jadwal_pelajaran_id' => $schedule->id,
                    'santri_id' => $santriId,
                    'tanggal' => $tanggal,
                ],
                [
                    'status_kehadiran' => $data['status'],
                    'keterangan' => filled($data['keterangan'] ?? null) ? trim($data['keterangan']) : null,
                    'created_by' => Auth::id(),
                ]
            );
        }

        return redirect()->route('teacher.attendance.index')->with('success', "Presensi santri tanggal {$tanggal} berhasil disimpan.");
    }

    private function authorizeScheduleAccess(JadwalPelajaran $schedule): void
    {
        $user = Auth::user();
        if ($user->isTenagaPengajar() && $schedule->pengajar_id !== ($user->pengajar?->id ?? 0)) {
            abort(403, 'Anda hanya dapat mengelola presensi pada jadwal mengajar Anda sendiri.');
        }
    }
}
