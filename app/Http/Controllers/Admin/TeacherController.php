<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengajar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengajar::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nomor_identitas', 'like', "%{$search}%")
                  ->orWhere('nama_lengkap', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status_pengajar', $request->status);
        }

        $teachers = $query->orderBy('nama_lengkap', 'asc')->paginate(10);
        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('admin.teachers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_identitas' => 'required|string|max:30|unique:pengajars,nomor_identitas',
            'nama_lengkap' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'nomor_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'status_pengajar' => 'required|in:Aktif,Non-Aktif',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['nama_lengkap'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => User::ROLE_TENAGA_PENGAJAR,
            ]);

            Pengajar::create([
                'user_id' => $user->id,
                'nomor_identitas' => $validated['nomor_identitas'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'nomor_telepon' => $validated['nomor_telepon'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'status_pengajar' => $validated['status_pengajar'],
            ]);
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Data Pengajar / Ustaz berhasil ditambahkan.');
    }

    public function edit(Pengajar $teacher)
    {
        $teacher->load('user');
        return view('admin.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, Pengajar $teacher)
    {
        $validated = $request->validate([
            'nomor_identitas' => 'required|string|max:30|unique:pengajars,nomor_identitas,' . $teacher->id,
            'nama_lengkap' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'email' => 'required|email|unique:users,email,' . ($teacher->user_id ?? 0),
            'password' => 'nullable|string|min:6',
            'nomor_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'status_pengajar' => 'required|in:Aktif,Non-Aktif',
        ]);

        DB::transaction(function () use ($teacher, $validated) {
            if ($teacher->user) {
                $userData = [
                    'name' => $validated['nama_lengkap'],
                    'email' => $validated['email'],
                ];
                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }
                $teacher->user->update($userData);
            }

            $teacher->update([
                'nomor_identitas' => $validated['nomor_identitas'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'nomor_telepon' => $validated['nomor_telepon'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'status_pengajar' => $validated['status_pengajar'],
            ]);
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Data Pengajar berhasil diperbarui.');
    }

    public function destroy(Pengajar $teacher)
    {
        DB::transaction(function () use ($teacher) {
            $user = $teacher->user;
            $teacher->delete();
            if ($user) {
                $user->delete();
            }
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Data Pengajar berhasil dihapus.');
    }
}
