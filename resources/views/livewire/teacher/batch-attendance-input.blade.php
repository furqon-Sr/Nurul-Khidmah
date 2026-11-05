<div class="max-w-5xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 id="attendance-table-heading" class="text-2xl font-bold text-gray-800 dark:text-gray-100">Input Presensi Harian &amp; Catatan Disiplin</h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm">
                {{ $jadwal->mataPelajaran->nama_mapel ?? '-' }} — {{ $jadwal->kelas->nama_kelas ?? '-' }}
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center gap-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-2.5 py-1.5 text-xs shadow-sm">
                <label for="pilih-tanggal" class="text-gray-600 dark:text-gray-400 font-semibold">Tanggal:</label>
                <input id="pilih-tanggal" type="date" wire:model.live="tanggal"
                       class="text-xs font-semibold bg-transparent border-0 p-0 focus:ring-0 text-gray-800 dark:text-gray-200 cursor-pointer">
            </div>

            @if(!empty($attendances))
                <button type="button" wire:click="markAll('Hadir')"
                        class="px-3.5 py-2 bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 rounded-lg hover:bg-emerald-200 transition text-xs font-semibold">
                    ✓ Tandai Semua Hadir
                </button>
            @endif
            <a href="{{ route('teacher.attendance.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition text-sm font-medium">
                ← Kembali
            </a>
        </div>
    </div>

    {{-- Feedback Alerts --}}
    @if (session()->has('success') || $hasSaved)
        <div x-data="{ show: true }" x-show="show" class="mb-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2 text-sm font-medium">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('success') ?? $feedbackMessage }}</span>
            </div>
            <button type="button" @click="show = false" class="text-emerald-600 dark:text-emerald-400 hover:opacity-75 text-xs font-semibold">✕ Tutup</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm">
            <div class="font-semibold mb-1">Periksa kembali data presensi:</div>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (!empty($lockedSantris))
        <div class="mb-4 p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-300 text-xs flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
            </svg>
            <span>
                Terdapat <strong>{{ count($lockedSantris) }} santri</strong> yang rapor semesternya sudah disetujui (Approved) dan terkunci permanen. Presensi mereka tidak dapat diubah lagi.
            </span>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border-b border-emerald-100 dark:border-emerald-900/40 flex flex-wrap justify-between items-center text-sm gap-2">
            <span class="font-semibold text-emerald-900 dark:text-emerald-300">
                Santri Aktif: {{ count($attendances) + count($lockedSantris) }} Orang (Dapat diedit: {{ count($attendances) }})
            </span>
            <span class="text-xs text-emerald-700 dark:text-emerald-400">
                Pengajar: {{ $jadwal->pengajar->nama_lengkap ?? '-' }} • Periode: {{ date('d F Y', strtotime($tanggal)) }}
            </span>
        </div>

        <div tabindex="0" role="region" aria-labelledby="attendance-table-heading" class="overflow-x-auto max-h-[75vh]">
            <table class="min-w-full border-separate border-spacing-0">
                <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700">NIS &amp; Nama Santri</th>
                        <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700">Status Kehadiran</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700">Catatan Disiplin / Keterangan</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($attendances as $santriId => $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition" wire:key="attendance-row-{{ $santriId }}">
                            <th scope="row" class="sticky left-0 z-[1] bg-white dark:bg-gray-800 px-6 py-4 whitespace-nowrap text-left font-normal border-b border-gray-100 dark:border-gray-700/60">
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $row['nama_lengkap'] }}</div>
                                <div class="text-xs text-gray-500 font-mono mt-0.5">{{ $row['nis'] }}</div>
                            </th>
                            <td class="px-6 py-4 whitespace-nowrap text-center border-b border-gray-100 dark:border-gray-700/60">
                                <div class="inline-flex items-center gap-3">
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="radio"
                                               wire:model.defer="attendances.{{ $santriId }}.status"
                                               value="Hadir"
                                               class="attendance-hadir-radio text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">Hadir</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="radio"
                                               wire:model.defer="attendances.{{ $santriId }}.status"
                                               value="Izin"
                                               class="text-blue-600 focus:ring-blue-500">
                                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-400">Izin</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="radio"
                                               wire:model.defer="attendances.{{ $santriId }}.status"
                                               value="Sakit"
                                               class="text-amber-600 focus:ring-amber-500">
                                        <span class="text-xs font-semibold text-amber-700 dark:text-amber-400">Sakit</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="radio"
                                               wire:model.defer="attendances.{{ $santriId }}.status"
                                               value="Alpa"
                                               class="text-rose-600 focus:ring-rose-500">
                                        <span class="text-xs font-semibold text-rose-700 dark:text-rose-400">Alpa</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="radio"
                                               wire:model.defer="attendances.{{ $santriId }}.status"
                                               value="Terlambat"
                                               class="text-orange-600 focus:ring-orange-500">
                                        <span class="text-xs font-semibold text-orange-700 dark:text-orange-400">Terlambat</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="radio"
                                               wire:model.defer="attendances.{{ $santriId }}.status"
                                               value="Bolos"
                                               class="text-purple-600 focus:ring-purple-500">
                                        <span class="text-xs font-semibold text-purple-700 dark:text-purple-400">Bolos</span>
                                    </label>
                                </div>
                            </td>
                            <td class="px-6 py-3 min-w-[270px] border-b border-gray-100 dark:border-gray-700/60"
                                x-data="{
                                    addNote(text) {
                                        let current = $refs.ketInput.value;
                                        $refs.ketInput.value = current ? (current + '; ' + text) : text;
                                        $refs.ketInput.dispatchEvent(new Event('input'));
                                    }
                                }">
                                <input type="text"
                                       x-ref="ketInput"
                                       wire:model.defer="attendances.{{ $santriId }}.keterangan"
                                       placeholder="Misal: Terlambat 15 mnt / meninggalkan jam..."
                                       class="w-full px-3 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs focus:ring-2 focus:ring-emerald-500">
                                <div class="flex flex-wrap gap-1 mt-1.5">
                                    <button type="button" @click="addNote('Terlambat masuk kelas')" class="text-[10px] px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 hover:bg-amber-100">+ Terlambat</button>
                                    <button type="button" @click="addNote('Meninggalkan jam pelajaran')" class="text-[10px] px-2 py-0.5 rounded bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 hover:bg-rose-100">+ Meninggalkan Kelas</button>
                                    <button type="button" @click="addNote('Izin Poskestren / Tugas Pondok')" class="text-[10px] px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 hover:bg-blue-100">+ Tugas/Poskestren</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @if(empty($lockedSantris))
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-gray-500 text-sm">
                                    Tidak ada santri aktif terdaftar di kelas ini.
                                </td>
                            </tr>
                        @endif
                    @endforelse

                    {{-- Baris Santri Terkunci / Approved --}}
                    @foreach($lockedSantris as $lockedId => $locked)
                        <tr class="bg-gray-50/70 dark:bg-gray-800/50 opacity-80" wire:key="locked-absen-{{ $lockedId }}">
                            <th scope="row" class="sticky left-0 z-[1] bg-gray-50/90 dark:bg-gray-800/90 px-6 py-4 whitespace-nowrap text-left font-normal border-b border-gray-100 dark:border-gray-700/60">
                                <div class="font-semibold text-gray-700 dark:text-gray-300">{{ $locked['nama_lengkap'] }}</div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs text-gray-500 font-mono">{{ $locked['nis'] }}</span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800 shadow-sm">
                                        <svg class="w-3 h-3 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                        </svg>
                                        Terkunci (Approved)
                                    </span>
                                </div>
                            </th>
                            <td class="px-6 py-4 whitespace-nowrap text-center border-b border-gray-100 dark:border-gray-700/60">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    {{ $locked['status'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/60 text-xs text-gray-500 italic">
                                {{ $locked['keterangan'] ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if(!empty($attendances))
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                Penyimpanan presensi memanfaatkan <strong>batch upsert</strong> (1 single query atomic) tanpa N+1 overhead.
            </div>

            <div class="flex justify-end items-center gap-3">
                <a href="{{ route('teacher.attendance.index') }}" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                    Batal
                </a>
                <button type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg transition text-sm font-semibold shadow-sm">
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Simpan Presensi</span>
                </button>
            </div>
        </div>
    @elseif(!empty($lockedSantris))
        <div class="flex items-center gap-2 px-4 py-3 rounded-lg bg-amber-50 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs font-semibold">
            <svg class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
            </svg>
            Presensi santri pada periode ini telah disetujui (Approved) dan terkunci permanen.
        </div>
    @endif
</div>
