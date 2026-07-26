<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Santri;
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
            $search = $request->search;
            $query->where('nis', 'like', "%{$search}%")
                  ->orWhere('nama_lengkap', 'like', "%{$search}%");
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('status')) {
            $query->where('status_santri', $request->status);
        }

        $santris = $query->orderBy('nama_lengkap', 'asc')->paginate(10);
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
            'nama_wali' => 'nullable|string|max:100',
            'tahun_masuk' => 'nullable|string|max:4',
            'status_santri' => 'required|in:Aktif,Alumni,Non-Aktif',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['nama_lengkap'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => User::ROLE_SANTRI,
            ]);

            Santri::create([
                'user_id' => $user->id,
                'kelas_id' => $validated['kelas_id'] ?? null,
                'nis' => $validated['nis'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'alamat' => $validated['alamat'] ?? null,
                'nama_wali' => $validated['nama_wali'] ?? null,
                'tahun_masuk' => $validated['tahun_masuk'] ?? date('Y'),
                'status_santri' => $validated['status_santri'],
            ]);
        });

        return redirect()->route('admin.santri.index')->with('success', 'Data Santri berhasil ditambahkan.');
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
            'nama_wali' => 'nullable|string|max:100',
            'tahun_masuk' => 'nullable|string|max:4',
            'status_santri' => 'required|in:Aktif,Alumni,Non-Aktif',
        ]);

        DB::transaction(function () use ($santri, $validated) {
            if ($santri->user) {
                $userData = [
                    'name' => $validated['nama_lengkap'],
                    'email' => $validated['email'],
                ];
                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }
                $santri->user->update($userData);
            }

            $santri->update([
                'kelas_id' => $validated['kelas_id'] ?? null,
                'nis' => $validated['nis'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'alamat' => $validated['alamat'] ?? null,
                'nama_wali' => $validated['nama_wali'] ?? null,
                'tahun_masuk' => $validated['tahun_masuk'] ?? null,
                'status_santri' => $validated['status_santri'],
            ]);
        });

        return redirect()->route('admin.santri.index')->with('success', 'Data Santri berhasil diperbarui.');
    }

    public function destroy(Santri $santri)
    {
        DB::transaction(function () use ($santri) {
            $user = $santri->user;
            $santri->delete();
            if ($user) {
                $user->delete();
            }
        });

        return redirect()->route('admin.santri.index')->with('success', 'Data Santri berhasil dihapus.');
    }
}
