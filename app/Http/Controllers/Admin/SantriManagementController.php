<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Kelas;
use App\Models\RaporSemester;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SantriManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Santri::with(['user', 'kelas']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nama_wali', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('status')) {
            if (in_array($request->status, ['Lulus', 'Alumni'], true)) {
                $query->whereIn('status_santri', ['Lulus', 'Alumni']);
            } elseif (in_array($request->status, ['Mutasi', 'Non-Aktif'], true)) {
                $query->whereIn('status_santri', ['Mutasi', 'Non-Aktif']);
            } else {
                $query->where('status_santri', $request->status);
            }
        }

        $santris = $query->orderBy('nama_lengkap', 'asc')->paginate(10)->withQueryString();
        $kelasses = Kelas::orderBy('nama_kelas')->get();

        return view('admin.santri.index', compact('santris', 'kelasses'));
    }

    public function create()
    {
        $kelasses = Kelas::orderBy('nama_kelas')->get();

        return view('admin.santri.create', compact('kelasses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'required|string|max:20|unique:santris,nis',
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'kelas_id' => 'nullable|exists:kelasses,id',
            'tempat_lahir' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'alamat' => 'nullable|string',
            'nama_wali' => 'nullable|string|max:255',
            'no_hp_wali' => 'nullable|string|max:30',
            'tahun_masuk' => 'nullable|string|max:4',
            'status_santri' => 'required|in:Aktif,Lulus,Alumni,Mutasi,Non-Aktif',
            'nilai_sikap' => 'nullable|string|max:50',
            'catatan_wali_kelas' => 'nullable|string',
        ]);

        $santri = DB::transaction(function () use ($validated) {
            $waliDisplayName = ! empty($validated['nama_wali'])
                ? $validated['nama_wali'] . ' (Wali ' . $validated['nama_lengkap'] . ')'
                : 'Wali ' . $validated['nama_lengkap'];

            $user = User::create([
                'name' => $waliDisplayName,
                'email' => strtolower(trim($validated['email'])),
                'password' => Hash::make($validated['password']),
                'role' => User::ROLE_SANTRI,
                'email_verified_at' => now(),
            ]);

            $eduUnitId = null;
            if (!empty($validated['kelas_id'])) {
                $eduUnitId = Kelas::where('id', $validated['kelas_id'])->value('educational_unit_id');
            }

            $santriRecord = Santri::create([
                'user_id' => $user->id,
                'kelas_id' => $validated['kelas_id'] ?? null,
                'educational_unit_id' => $eduUnitId,
                'nis' => $validated['nis'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'alamat' => $validated['alamat'] ?? null,
                'nama_wali' => $validated['nama_wali'] ?? null,
                'no_hp_wali' => $validated['no_hp_wali'] ?? null,
                'tahun_masuk' => $validated['tahun_masuk'] ?? date('Y'),
                'status_santri' => $validated['status_santri'],
            ]);

            if (filled($validated['nilai_sikap'] ?? null) || filled($validated['catatan_wali_kelas'] ?? null)) {
                $targetTaId = null;
                if (!empty($validated['kelas_id'])) {
                    $targetTaId = Kelas::where('id', $validated['kelas_id'])->value('tahun_ajaran_id');
                }
                $targetTaId = $targetTaId ?: TahunAjaran::getAktif()?->id;

                if ($targetTaId) {
                    RaporSemester::updateOrCreate(
                        ['santri_id' => $santriRecord->id, 'tahun_ajaran_id' => $targetTaId],
                        [
                            'kelas_id' => $validated['kelas_id'] ?? null,
                            'nilai_sikap' => $validated['nilai_sikap'] ?? null,
                            'catatan_wali_kelas' => $validated['catatan_wali_kelas'] ?? null,
                            'status_publikasi' => 'published',
                        ]
                    );
                }
            }

            return $santriRecord;
        });

        AdminActivityLog::log('create', "Menambahkan data master santri: {$santri->nama_lengkap} ({$santri->nis})", 'Santri', $santri->id);

        return redirect()->route('admin.santri.index')->with('success', 'Data Santri & Akun Portal Wali berhasil ditambahkan.');
    }

    public function edit(Santri $santri)
    {
        $santri->load('user');
        $kelasses = Kelas::orderBy('nama_kelas')->get();

        return view('admin.santri.edit', compact('santri', 'kelasses'));
    }

    public function update(Request $request, Santri $santri)
    {
        $validated = $request->validate([
            'nis' => 'required|string|max:20|unique:santris,nis,' . $santri->id,
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . ($santri->user_id ?? 0),
            'password' => 'nullable|string|min:6',
            'kelas_id' => 'nullable|exists:kelasses,id',
            'tempat_lahir' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'alamat' => 'nullable|string',
            'nama_wali' => 'nullable|string|max:255',
            'no_hp_wali' => 'nullable|string|max:30',
            'tahun_masuk' => 'nullable|string|max:4',
            'status_santri' => 'required|in:Aktif,Lulus,Alumni,Mutasi,Non-Aktif',
            'nilai_sikap' => 'nullable|string|max:50',
            'catatan_wali_kelas' => 'nullable|string',
        ]);

        DB::transaction(function () use ($santri, $validated) {
            if ($santri->user) {
                $waliDisplayName = ! empty($validated['nama_wali'])
                    ? $validated['nama_wali'] . ' (Wali ' . $validated['nama_lengkap'] . ')'
                    : $validated['nama_lengkap'];

                $userData = [
                    'name' => $waliDisplayName,
                    'email' => strtolower(trim($validated['email'])),
                ];
                if (! empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }
                if (! $santri->user->email_verified_at) {
                    $userData['email_verified_at'] = now();
                }
                $santri->user->update($userData);
            }

            $eduUnitId = $santri->educational_unit_id;
            if (!empty($validated['kelas_id'])) {
                $eduUnitId = Kelas::where('id', $validated['kelas_id'])->value('educational_unit_id') ?? $eduUnitId;
            }

            $santri->update([
                'kelas_id' => $validated['kelas_id'] ?? null,
                'educational_unit_id' => $eduUnitId,
                'nis' => $validated['nis'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'alamat' => $validated['alamat'] ?? null,
                'nama_wali' => $validated['nama_wali'] ?? null,
                'no_hp_wali' => $validated['no_hp_wali'] ?? null,
                'tahun_masuk' => $validated['tahun_masuk'] ?? null,
                'status_santri' => $validated['status_santri'],
            ]);

            if (filled($validated['nilai_sikap'] ?? null) || filled($validated['catatan_wali_kelas'] ?? null)) {
                $targetTaId = null;
                if (!empty($validated['kelas_id'])) {
                    $targetTaId = Kelas::where('id', $validated['kelas_id'])->value('tahun_ajaran_id');
                }
                $targetTaId = $targetTaId ?: ($santri->kelas?->tahun_ajaran_id ?? TahunAjaran::getAktif()?->id);

                if ($targetTaId) {
                    RaporSemester::updateOrCreate(
                        ['santri_id' => $santri->id, 'tahun_ajaran_id' => $targetTaId],
                        [
                            'kelas_id' => $validated['kelas_id'] ?? $santri->kelas_id,
                            'nilai_sikap' => $validated['nilai_sikap'] ?? null,
                            'catatan_wali_kelas' => $validated['catatan_wali_kelas'] ?? null,
                            'status_publikasi' => 'published',
                        ]
                    );
                }
            }
        });

        AdminActivityLog::log('update', "Memperbarui data santri: {$santri->nama_lengkap} ({$santri->nis})", 'Santri', $santri->id);

        return redirect()->route('admin.santri.index')->with('success', 'Data Santri berhasil diperbarui.');
    }

    public function destroy(Santri $santri)
    {
        $name = $santri->nama_lengkap;
        $nis = $santri->nis;

        DB::transaction(function () use ($santri) {
            $user = $santri->user;
            $santri->delete();
            if ($user) {
                $user->delete();
            }
        });

        AdminActivityLog::log('delete', "Menghapus data santri: {$name} ({$nis})", 'Santri');

        return redirect()->route('admin.santri.index')->with('success', 'Data Santri berhasil dihapus.');
    }
}
