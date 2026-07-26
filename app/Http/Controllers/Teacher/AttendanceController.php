<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JadwalPelajaran;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = JadwalPelajaran::with(['tahunAjaran', 'kelas', 'mataPelajaran', 'pengajar']);

        // If user is teacher, filter schedules by their teacher profile
        if ($user->isTenagaPengajar() && $user->pengajar) {
            $query->where('pengajar_id', $user->pengajar->id);
        }

        $schedules = $query->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
                           ->get();

        return view('teacher.attendance.index', compact('schedules'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:jadwal_pelajarans,id',
            'tanggal' => 'required|date',
        ]);

        $schedule = JadwalPelajaran::with(['kelas', 'mataPelajaran', 'pengajar', 'tahunAjaran'])->findOrFail($request->schedule_id);
        $tanggal = $request->tanggal;

        // Get santris in this class
        $santris = Santri::where('kelas_id', $schedule->kelas_id)
                         ->where('status_santri', 'Aktif')
                         ->orderBy('nama_lengkap')
                         ->get();

        // Get existing attendance records for this date & schedule
        $existingAbsensi = Absensi::where('jadwal_pelajaran_id', $schedule->id)
                                  ->where('tanggal', $tanggal)
                                  ->get()
                                  ->keyBy('santri_id');

        return view('teacher.attendance.create', compact('schedule', 'tanggal', 'santris', 'existingAbsensi'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jadwal_pelajaran_id' => 'required|exists:jadwal_pelajarans,id',
            'tanggal' => 'required|date',
            'absensi' => 'required|array',
            'absensi.*.status' => 'required|in:Hadir,Izin,Sakit,Alpa',
            'absensi.*.keterangan' => 'nullable|string',
        ]);

        $scheduleId = $validated['jadwal_pelajaran_id'];
        $tanggal = $validated['tanggal'];

        foreach ($validated['absensi'] as $santriId => $data) {
            Absensi::updateOrCreate(
                [
                    'jadwal_pelajaran_id' => $scheduleId,
                    'santri_id' => $santriId,
                    'tanggal' => $tanggal,
                ],
                [
                    'status_kehadiran' => $data['status'],
                    'keterangan' => $data['keterangan'] ?? null,
                    'created_by' => Auth::id(),
                ]
            );
        }

        return redirect()->route('teacher.attendance.index')->with('success', "Absensi santri tanggal {$tanggal} berhasil disimpan.");
    }
}
