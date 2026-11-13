<div x-data="{ showPhotoModal: false, activeSantri: null }" class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Manajemen Data Santri</h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Kelola data induk santri, kelas, dan akun portal wali santri. Klik foto atau nama untuk melihat profil lengkap.</p>
        </div>
        <a href="{{ route('admin.santri.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition shadow-sm font-medium text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Santri Baru
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-100 dark:bg-emerald-950/50 border-l-4 border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded shadow-sm text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        {{-- Filter & Search Bar Livewire --}}
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
            <div class="flex flex-wrap items-center gap-3">
                {{-- Input Pencarian Realtime --}}
                <div class="relative w-full max-w-xs">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" 
                           wire:model.live.debounce.250ms="search"
                           x-on:input="
                               const q = $event.target.value.toLowerCase().trim();
                               document.querySelectorAll('.santri-row-item').forEach(r => {
                                   const t = (r.getAttribute('data-search') || '').toLowerCase();
                                   r.style.display = (!q || t.includes(q)) ? '' : 'none';
                               });
                           "
                           placeholder="Ketik NIS, NISN, Nama, Wali..."
                           autocorrect="off" autocapitalize="off" spellcheck="false"
                           class="w-full min-h-11 pl-9 pr-9 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm focus:ring-2 focus:ring-emerald-500 text-gray-900 dark:text-gray-100 placeholder-gray-400">
                    
                    @if(!empty($search))
                        <button type="button" 
                                wire:click="$set('search', '')" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs font-bold"
                                title="Hapus pencarian">
                            &times;
                        </button>
                    @endif
                </div>

                {{-- Filter Kelas --}}
                <select wire:model.live="kelas_id" 
                        class="min-h-11 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm focus:ring-2 focus:ring-emerald-500 text-gray-900 dark:text-gray-100">
                    <option value="">-- Semua Kelas --</option>
                    <option value="unassigned">-- Mondok Saja / Belum Masuk Kelas --</option>
                    @foreach($kelasses as $k)
                        <option value="{{ $k->id }}">
                            {{ $k->nama_kelas }} ({{ $k->tahunAjaran->nama_tahun_ajaran ?? '-' }})
                        </option>
                    @endforeach
                </select>

                {{-- Filter Status Santri --}}
                <select wire:model.live="status" 
                        class="min-h-11 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm focus:ring-2 focus:ring-emerald-500 text-gray-900 dark:text-gray-100">
                    <option value="">-- Semua Status --</option>
                    <option value="Aktif">Aktif</option>
                    <option value="Lulus">Lulus / Alumni</option>
                    <option value="Mutasi">Mutasi / Non-Aktif</option>
                </select>

                {{-- Reset Filter Button --}}
                @if(!empty($search) || !empty($kelas_id) || !empty($status))
                    <button type="button" 
                            wire:click="resetFilters" 
                            class="min-h-11 px-3 py-2 inline-flex items-center text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 hover:underline">
                        Reset Filter
                    </button>
                @endif

                {{-- Livewire Loading Indicator --}}
                <div wire:loading.delay wire:target="search, kelas_id, status" class="inline-flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                    <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span>Menyaring realtime...</span>
                </div>

                {{-- Result Count Badge --}}
                <span class="ml-auto text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                    Total: {{ $santris->total() }} santri
                </span>
            </div>
        </div>

        {{-- Tabel Data Santri --}}
        <div class="overflow-x-auto">
            <table class="table-builder min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400">NIS &amp; NISN</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400">Santri &amp; Foto</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400">Kelas</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400">Nama Wali &amp; Kontak</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-600 dark:text-gray-400">Status</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-600 dark:text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($santris as $item)
                    @php
                        $santriPayload = [
                            'nama' => $item->nama_lengkap,
                            'nis' => $item->nis,
                            'nisn' => $item->nisn ?? '-',
                            'foto' => $item->fotoUrl(),
                            'initials' => $item->initials(),
                            'kelas' => $item->kelas->nama_kelas ?? ($item->previous_school ?: 'Mondok Saja (Non-Kelas Formal)'),
                            'tahun_ajaran' => $item->kelas?->tahunAjaran?->nama_tahun_ajaran ?? '-',
                            'status' => $item->status_santri,
                            'gender' => $item->genderLabel(),
                            'ttl' => ($item->tempat_lahir ? $item->tempat_lahir . ', ' : '') . ($item->tanggal_lahir ? $item->tanggal_lahir->format('d M Y') : '-'),
                            'wali' => $item->nama_wali ?? '-',
                            'no_hp' => $item->no_hp_wali ?? '-',
                            'alamat' => $item->alamat ?? '-',
                            'asal_sekolah' => $item->previous_school ?? '-',
                            'mukim' => $item->boarding_status ? (strtolower((string) $item->boarding_status) === 'mukim' ? 'Mukim di Pondok' : 'Tidak Mukim (Laju)') : '-',
                            'edit_url' => route('admin.santri.edit', $item->id),
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150 santri-row-item" data-search="{{ strtolower($item->nama_lengkap . ' ' . $item->nis . ' ' . ($item->nisn ?? '') . ' ' . ($item->nama_wali ?? '') . ' ' . ($item->no_hp_wali ?? '')) }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-xs font-bold px-2.5 py-1 rounded bg-purple-50 text-purple-800 dark:bg-purple-950 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                {{ $item->nis }}
                            </span>
                            @if($item->nisn)
                                <div class="text-[11px] font-mono text-gray-600 dark:text-gray-400 mt-1">
                                    NISN: {{ $item->nisn }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <button type="button" 
                                        @click="activeSantri = {{ json_encode($santriPayload) }}; showPhotoModal = true"
                                        class="shrink-0 relative group focus:outline-none focus:ring-2 focus:ring-emerald-500 rounded-full"
                                        title="Klik untuk melihat foto profil santri">
                                    @if($item->fotoUrl())
                                        <img src="{{ $item->fotoUrl() }}" 
                                             alt="{{ $item->nama_lengkap }}" 
                                             class="w-10 h-10 rounded-full object-cover group-hover:scale-105 transition duration-150 shadow-sm">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 dark:bg-primary-500/20 dark:text-primary-300 font-bold flex items-center justify-center text-xs group-hover:scale-105 transition duration-150 shadow-sm">
                                            {{ $item->initials() }}
                                        </div>
                                    @endif
                                    <span class="absolute -bottom-1 -right-1 bg-white dark:bg-gray-900 rounded-full p-0.5 shadow">
                                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
                                    </span>
                                </button>
                                <div>
                                    <button type="button" 
                                            @click="activeSantri = {{ json_encode($santriPayload) }}; showPhotoModal = true"
                                            class="text-left font-semibold text-gray-900 dark:text-gray-100 hover:text-emerald-600 dark:hover:text-emerald-400 transition"
                                            title="Klik untuk membuka profil">
                                        {{ $item->nama_lengkap }}
                                    </button>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                        {{ $item->genderLabel() }} • Masuk: {{ $item->tahun_masuk ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 font-medium">
                            @if($item->kelas)
                                <div>{{ $item->kelas->nama_kelas }}</div>
                                @if($item->kelas->tahunAjaran)
                                    <div class="text-xs text-gray-600 dark:text-gray-400">TA: {{ $item->kelas->tahunAjaran->nama_tahun_ajaran }}</div>
                                @endif
                            @else
                                <div>Mondok Saja</div>
                                @if($item->previous_school)
                                    <div class="text-xs text-emerald-700 dark:text-emerald-400">{{ $item->previous_school }}</div>
                                @endif
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                            <div class="font-medium text-gray-800 dark:text-gray-200">{{ $item->nama_wali ?? '-' }}</div>
                            @if($item->no_hp_wali)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $item->no_hp_wali) }}" target="_blank" class="text-xs text-emerald-600 hover:underline inline-flex items-center gap-1">
                                    {{ $item->no_hp_wali }}
                                </a>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2.5 py-1 text-2xs uppercase tracking-wide font-bold rounded-md {{ $item->status_santri === 'Aktif' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : (in_array($item->status_santri, ['Lulus', 'Alumni']) ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300') }}">
                                {{ $item->status_santri === 'Alumni' ? 'Lulus (Alumni)' : $item->status_santri }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button type="button" @click="activeSantri = {{ json_encode($santriPayload) }}; showPhotoModal = true" title="Lihat profil" aria-label="Lihat profil" class="icon-btn icon-btn-view mr-1.5"><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></button>
                            <x-icon-button type="edit" href="{{ route('admin.santri.edit', $item->id) }}" class="mr-1.5" />
                            <form action="{{ route('admin.santri.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Menghapus data santri akan sekaligus menghapus akun user terhubung. Yakin?');">
                                @csrf
                                @method('DELETE')
                                <x-icon-button type="delete" :submit="true" />
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-600 dark:text-gray-400 text-sm">
                            Belum ada data santri yang cocok dengan filter atau kata kunci pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($santris->hasPages())
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-200 dark:border-gray-700">
            {{ $santris->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Profil & Pasfoto Santri (Alpine.js) -->
    <div x-show="showPhotoModal" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         @keydown.escape.window="showPhotoModal = false">
        
        <div @click.away="showPhotoModal = false" 
             x-show="showPhotoModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
             class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 max-w-lg w-full overflow-hidden">
            
            <!-- Header Modal -->
            <div class="px-6 py-4 bg-emerald-800 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <h3 class="font-bold text-base">Profil &amp; Pasfoto Santri</h3>
                </div>
                <button type="button" @click="showPhotoModal = false" class="text-white/80 hover:text-white text-xl font-bold leading-none p-1 focus:outline-none">
                    &times;
                </button>
            </div>

            <!-- Body Modal -->
            <div class="p-6 space-y-6" x-show="activeSantri">
                <div class="flex flex-col items-center text-center">
                    <template x-if="activeSantri && activeSantri.foto">
                        <img :src="activeSantri.foto" 
                             :alt="activeSantri.nama" 
                             x-on:error="activeSantri.foto = null"
                             class="w-36 h-48 object-cover rounded-xl shadow-lg border-4 border-emerald-600/30">
                    </template>
                    <template x-if="activeSantri && !activeSantri.foto">
                        <div class="w-36 h-48 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-800 text-white font-extrabold flex flex-col items-center justify-center shadow-lg border-4 border-emerald-600/30">
                            <span class="text-4xl" x-text="activeSantri ? activeSantri.initials : 'S'"></span>
                            <span class="text-[11px] mt-2 font-normal opacity-80">(Belum ada pasfoto)</span>
                        </div>
                    </template>

                    <h4 class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-4" x-text="activeSantri ? activeSantri.nama : ''"></h4>
                    
                    <div class="mt-1 flex flex-wrap items-center justify-center gap-2">
                        <span class="px-2.5 py-1 rounded-md text-2xs uppercase tracking-wide font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300" x-text="activeSantri ? activeSantri.status : ''"></span>
                        <span class="text-xs text-gray-600 dark:text-gray-400 font-mono" x-text="activeSantri ? ('NIS: ' + activeSantri.nis) : ''"></span>
                    </div>
                </div>

                <!-- Detail Grid -->
                <div class="grid grid-cols-2 gap-3 p-4 bg-gray-50 dark:bg-gray-800/60 rounded-xl text-xs">
                    <div>
                        <span class="text-gray-600 dark:text-gray-400 block font-medium">NISN</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="activeSantri ? activeSantri.nisn : '-'"></span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400 block font-medium">Kelas &amp; TA</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="activeSantri ? (activeSantri.kelas + ' (' + activeSantri.tahun_ajaran + ')') : '-'"></span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400 block font-medium">Jenis Kelamin</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="activeSantri ? activeSantri.gender : '-'"></span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400 block font-medium">Tempat, Tanggal Lahir</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="activeSantri ? activeSantri.ttl : '-'"></span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400 block font-medium">Wali Santri</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="activeSantri ? activeSantri.wali : '-'"></span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400 block font-medium">No. Telepon / WA</span>
                        <span class="font-semibold text-emerald-600" x-text="activeSantri ? activeSantri.no_hp : '-'"></span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400 block font-medium">Asal Sekolah</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="activeSantri ? activeSantri.asal_sekolah : '-'"></span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400 block font-medium">Status Mukim</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="activeSantri ? activeSantri.mukim : '-'"></span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-gray-600 dark:text-gray-400 block font-medium">Alamat Lengkap</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="activeSantri ? activeSantri.alamat : '-'"></span>
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <a :href="activeSantri ? activeSantri.edit_url : '#'" 
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Edit Data Santri
                </a>
                <button type="button" @click="showPhotoModal = false" 
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg text-xs font-semibold transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
