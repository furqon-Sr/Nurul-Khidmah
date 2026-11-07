@extends('layouts.admin')
@section('title', 'Program Pendidikan')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">Program Pendidikan</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Kelola data dan informasi program pendidikan pondok pesantren.</p>
        </div>
        <a href="{{ route('admin.programs.create') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-500/20 dark:hover:bg-emerald-500/30 text-white dark:text-emerald-300 text-sm font-semibold rounded-xl shadow-sm border border-transparent dark:border-emerald-500/30 transition-all duration-200 hover:shadow-md focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Program
        </a>
    </div>

    <!-- Success Alert -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-3 animate-fade-in">
            <svg class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="text-emerald-800 text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Warning Alert (migration) -->
    @if(session('warning'))
        <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
            <div>
                <p class="text-amber-800 text-sm font-bold mb-1">⚠️ Migration belum dijalankan</p>
                <p class="text-amber-700 text-sm">Jalankan perintah berikut di terminal:</p>
                <code class="block mt-2 bg-gray-800 text-green-400 p-3 rounded-lg text-xs font-mono">cd C:\Users\jawa\Herd\nurulkhidmah<br>php artisan migrate</code>
                <p class="mt-2 text-xs text-amber-600">Setelah selesai, refresh halaman ini.</p>
            </div>
        </div>
    @endif

    <!-- Programs Card Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($programs as $program)
            <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-lg dark:hover:shadow-gray-900/50 transition-all duration-300 flex flex-col" id="card-{{ $program->id }}">

                <!-- Image Area -->
                <div class="aspect-[16/10] bg-gray-100 dark:bg-gray-700 relative overflow-hidden">
                    @if($program->image_path)
                        <img src="{{ asset('storage/' . $program->image_path) }}"
                             alt="{{ $program->name }}"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-gray-300 dark:text-gray-500">
                            <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <span class="text-xs mt-2 font-medium">Belum ada gambar</span>
                        </div>
                    @endif

                    <!-- Status Badges (Top-Right) -->
                    <div class="absolute top-3 right-3 flex flex-col gap-1.5">
                        @if($program->is_active)
                            <span data-badge="active" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/90 text-white shadow-sm backdrop-blur-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Aktif
                            </span>
                        @else
                            <span data-badge="active" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-500/80 text-white shadow-sm backdrop-blur-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span> Nonaktif
                            </span>
                        @endif

                        @if($program->is_registration_open)
                            <span data-badge="reg" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-500/90 text-white shadow-sm backdrop-blur-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Pendaftaran Buka
                            </span>
                        @endif

                        @if($program->is_coming_soon)
                            <span data-badge="coming-soon" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/90 text-white shadow-sm backdrop-blur-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Segera Hadir
                            </span>
                        @endif
                    </div>

                    <!-- Order Badge (Top-Left) -->
                    <div class="absolute top-3 left-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white/90 dark:bg-gray-800/90 text-gray-700 dark:text-gray-200 text-xs font-bold shadow-sm backdrop-blur-sm border border-white/50 dark:border-gray-700/50">
                            {{ $program->order ?: $loop->iteration }}
                        </span>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="p-5 flex-1 flex flex-col">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1 line-clamp-1" title="{{ $program->name }}">
                        {{ $program->name }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2 leading-relaxed">
                        {{ Str::limit(strip_tags($program->description), 100) ?: '—' }}
                    </p>

                    <!-- Action Footer -->
                    <div class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <!-- Toggle Switches -->
                        <div class="flex items-center gap-3">
                            {{-- Toggle: is_active --}}
                            <button onclick="toggleStatus({{ $program->id }}, 'is_active', this)"
                                    data-active="{{ $program->is_active ? 'true' : 'false' }}"
                                    class="relative inline-flex items-center h-6 w-11 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:focus:ring-offset-gray-800
                                           {{ $program->is_active ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"
                                    title="{{ $program->is_active ? 'Tampil — klik untuk sembunyikan' : 'Tersembunyi — klik untuk tampilkan' }}">
                                <span class="sr-only">Toggle Aktif</span>
                                <span class="inline-block w-4 h-4 bg-white rounded-full shadow-md transform transition-transform duration-200
                                             {{ $program->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>

                            {{-- Toggle: is_registration_open --}}
                            <button onclick="toggleStatus({{ $program->id }}, 'is_registration_open', this)"
                                    data-active="{{ $program->is_registration_open ? 'true' : 'false' }}"
                                    class="relative inline-flex items-center h-6 w-11 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800
                                           {{ $program->is_registration_open ? 'bg-blue-500' : 'bg-gray-300 dark:bg-gray-600' }}"
                                    title="{{ $program->is_registration_open ? 'Pendaftaran Terbuka — klik untuk tutup' : 'Pendaftaran Ditutup — klik untuk buka' }}">
                                <span class="sr-only">Toggle Pendaftaran</span>
                                <span class="inline-block w-4 h-4 bg-white rounded-full shadow-md transform transition-transform duration-200
                                             {{ $program->is_registration_open ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>

                            {{-- Toggle: is_coming_soon --}}
                            <button onclick="toggleStatus({{ $program->id }}, 'is_coming_soon', this)"
                                    data-active="{{ $program->is_coming_soon ? 'true' : 'false' }}"
                                    class="relative inline-flex items-center h-6 w-11 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 dark:focus:ring-offset-gray-800
                                           {{ $program->is_coming_soon ? 'bg-amber-500' : 'bg-gray-300 dark:bg-gray-600' }}"
                                    title="{{ $program->is_coming_soon ? 'Segera Hadir — klik untuk jadikan Unit Aktif' : 'Unit Aktif — klik untuk jadikan Segera Hadir' }}">
                                <span class="sr-only">Toggle Segera Hadir</span>
                                <span class="inline-block w-4 h-4 bg-white rounded-full shadow-md transform transition-transform duration-200
                                             {{ $program->is_coming_soon ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </div>

                        <!-- Edit & Delete Buttons -->
                        <div class="flex items-center gap-1">
                            <a href="{{ route('admin.programs.edit', $program) }}"
                               class="p-2 text-gray-400 dark:text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition-all duration-200"
                               title="Edit Program">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <form action="{{ route('admin.programs.destroy', $program) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Hapus program \'{{ $program->name }}\'? Tindakan ini tidak dapat dibatalkan.');">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-2 text-gray-400 dark:text-gray-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-all duration-200"
                                        title="Hapus Program">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="col-span-1 md:col-span-2 xl:col-span-3">
                <div class="flex flex-col items-center justify-center py-16 px-8 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 text-center">
                    <div class="w-20 h-20 bg-emerald-50 dark:bg-emerald-500/10 rounded-2xl flex items-center justify-center mb-5">
                        <svg class="w-10 h-10 text-emerald-400 dark:text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Belum Ada Program Pendidikan</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 max-w-sm">Mulai tambahkan program pendidikan pesantren untuk ditampilkan di halaman website.</p>
                    <a href="{{ route('admin.programs.create') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-500/20 dark:hover:bg-emerald-500/30 text-white dark:text-emerald-300 text-sm font-semibold rounded-xl shadow-sm border border-transparent dark:border-emerald-500/30 transition-all duration-200 hover:shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Program Pertama
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Legend -->
    @if($programs->count() > 0)
    <div class="mt-6 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-gray-600 dark:text-gray-400">
            <span class="font-semibold text-gray-600 dark:text-gray-300">Keterangan Toggle:</span>
            <span class="flex items-center gap-2">
                <span class="inline-block w-8 h-4 rounded-full bg-emerald-500"></span>
                <strong class="text-gray-700 dark:text-gray-300">Hijau</strong> = Tampil di website
            </span>
            <span class="flex items-center gap-2">
                <span class="inline-block w-8 h-4 rounded-full bg-blue-500"></span>
                <strong class="text-gray-700 dark:text-gray-300">Biru</strong> = Pendaftaran terbuka
            </span>
            <span class="flex items-center gap-2">
                <span class="inline-block w-8 h-4 rounded-full bg-amber-500"></span>
                <strong class="text-gray-700 dark:text-gray-300">Kuning</strong> = Segera hadir / Perencanaan
            </span>
            <span class="flex items-center gap-2">
                <span class="inline-block w-8 h-4 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                <strong class="text-gray-700 dark:text-gray-300">Abu</strong> = Mati / Nonaktif
            </span>
        </div>
    </div>
    @endif
</div>

<input type="hidden" id="csrf-token" value="{{ csrf_token() }}">

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.4s ease-out;
    }
    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<script>
async function toggleStatus(id, field, btn) {
    const token = document.getElementById('csrf-token').value;
    const isActive = btn.dataset.active === 'true';

    // Optimistic UI update
    const newVal = !isActive;
    applyToggleStyle(btn, field, newVal);
    btn.dataset.active = newVal ? 'true' : 'false';
    updateCardBadges(id, field, newVal);

    try {
        const res = await fetch(`/admin/programs/${id}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ field }),
        });

        if (!res.ok) throw new Error('Request failed');
        const data = await res.json();

        // Confirm with server response
        applyToggleStyle(btn, field, data.value);
        btn.dataset.active = data.value ? 'true' : 'false';
        updateCardBadges(id, field, data.value);

    } catch (e) {
        // Revert on failure
        applyToggleStyle(btn, field, isActive);
        btn.dataset.active = isActive ? 'true' : 'false';
        updateCardBadges(id, field, isActive);
        alert('Gagal memperbarui status. Silakan coba lagi.');
    }
}

function applyToggleStyle(btn, field, val) {
    const knob = btn.querySelector('span:not(.sr-only)');
    const activeColor = field === 'is_active' ? 'bg-emerald-500' : 'bg-blue-500';

    if (val) {
        btn.classList.remove('bg-gray-300');
        btn.classList.add(activeColor);
        knob.classList.remove('translate-x-1');
        knob.classList.add('translate-x-6');
    } else {
        btn.classList.remove('bg-emerald-500', 'bg-blue-500');
        btn.classList.add('bg-gray-300');
        knob.classList.remove('translate-x-6');
        knob.classList.add('translate-x-1');
    }
}

function updateCardBadges(id, field, val) {
    const card = document.getElementById('card-' + id);
    if (!card) return;

    const badgeContainer = card.querySelector('.absolute.top-3.right-3');
    if (!badgeContainer) return;

    if (field === 'is_active') {
        const activeBadge = badgeContainer.querySelector('span[data-badge="active"]');
        if (activeBadge) {
            if (val) {
                activeBadge.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/90 text-white shadow-sm backdrop-blur-sm';
                activeBadge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Aktif';
            } else {
                activeBadge.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-500/80 text-white shadow-sm backdrop-blur-sm';
                activeBadge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span> Nonaktif';
            }
        }
    }

    if (field === 'is_registration_open') {
        let regBadge = badgeContainer.querySelector('span[data-badge="reg"]');
        if (val) {
            if (!regBadge) {
                regBadge = document.createElement('span');
                regBadge.setAttribute('data-badge', 'reg');
                badgeContainer.appendChild(regBadge);
            }
            regBadge.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-500/90 text-white shadow-sm backdrop-blur-sm';
            regBadge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Pendaftaran Buka';
        } else {
            if (regBadge) regBadge.remove();
        }
    }

    if (field === 'is_coming_soon') {
        let soonBadge = badgeContainer.querySelector('span[data-badge="coming-soon"]');
        if (val) {
            if (!soonBadge) {
                soonBadge = document.createElement('span');
                soonBadge.setAttribute('data-badge', 'coming-soon');
                badgeContainer.appendChild(soonBadge);
            }
            soonBadge.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/90 text-white shadow-sm backdrop-blur-sm';
            soonBadge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Segera Hadir';
        } else {
            if (soonBadge) soonBadge.remove();
        }
    }
}
</script>
@endsection
