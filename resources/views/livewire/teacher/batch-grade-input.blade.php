<div class="max-w-6xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 id="grade-table-heading" class="text-2xl font-bold text-gray-800 dark:text-gray-100">Form Pengolahan Nilai &amp; Evaluasi Santri</h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm">
                {{ $jadwal->mataPelajaran->nama_mapel ?? '-' }} — {{ $jadwal->kelas->nama_kelas ?? '-' }} (Rumus Komponen: {{ $bobotTugas }}% Tugas + {{ $bobotUts }}% UTS + {{ $bobotUas }}% UAS)
            </p>
        </div>
        <a href="{{ route('teacher.grades.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition text-sm font-medium w-fit">
            ← Kembali
        </a>
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
            <div class="font-semibold mb-1">Periksa kembali data yang dimasukkan:</div>
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
                Terdapat <strong>{{ count($lockedSantris) }} santri</strong> yang rapor semesternya sudah disetujui (Approved) dan terkunci permanen. Baris mereka ditampilkan dalam mode baca saja (read-only).
            </span>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border-b border-emerald-100 dark:border-emerald-900/40 flex flex-wrap justify-between items-center text-sm gap-2">
            <span class="font-semibold text-emerald-900 dark:text-emerald-300">
                Santri Aktif: {{ count($grades) + count($lockedSantris) }} Orang (Dapat diedit: {{ count($grades) }})
            </span>
            <span class="text-xs text-emerald-700 dark:text-emerald-400">
                Batas Predikat:
                @foreach($predList as $p)
                    {{ $p['grade'] }} (≥{{ $p['min'] }}){{ ! $loop->last ? ' • ' : '' }}
                @endforeach
            </span>
        </div>

        <div tabindex="0" role="region" aria-labelledby="grade-table-heading" class="overflow-x-auto max-h-[75vh]">
            <table class="table-builder min-w-full border-separate border-spacing-0">
                <thead class="sticky top-0 z-10">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">NIS &amp; Nama Santri</th>
                        <th scope="col" class="px-3 py-3.5 text-center text-xs font-bold text-gray-600 dark:text-gray-400 w-28 border-b border-gray-200 dark:border-gray-700">Tugas ({{ $bobotTugas }}%)</th>
                        <th scope="col" class="px-3 py-3.5 text-center text-xs font-bold text-gray-600 dark:text-gray-400 w-28 border-b border-gray-200 dark:border-gray-700">UTS ({{ $bobotUts }}%)</th>
                        <th scope="col" class="px-3 py-3.5 text-center text-xs font-bold text-gray-600 dark:text-gray-400 w-28 border-b border-gray-200 dark:border-gray-700">UAS ({{ $bobotUas }}%)</th>
                        <th scope="col" class="px-3 py-3.5 text-center text-xs font-bold text-gray-600 dark:text-gray-400 w-28 border-b border-gray-200 dark:border-gray-700">Nilai Akhir</th>
                        <th scope="col" class="px-3 py-3.5 text-center text-xs font-bold text-gray-600 dark:text-gray-400 w-20 border-b border-gray-200 dark:border-gray-700">Grade</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400 min-w-[220px] border-b border-gray-200 dark:border-gray-700">Catatan Evaluasi / Sikap</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($grades as $santriId => $row)
                        @php
                            $totalB = ($bobotTugas + $bobotUts + $bobotUas) ?: 100;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                            wire:key="grade-row-{{ $santriId }}"
                            x-data="{
                                tugas: @entangle('grades.' . $santriId . '.nilai_tugas'),
                                uts: @entangle('grades.' . $santriId . '.nilai_uts'),
                                uas: @entangle('grades.' . $santriId . '.nilai_uas'),
                                preds: {{ json_encode($predList) }},
                                get hasInput() {
                                    return (this.tugas !== '' && this.tugas !== null && this.tugas !== undefined) ||
                                           (this.uts !== '' && this.uts !== null && this.uts !== undefined) ||
                                           (this.uas !== '' && this.uas !== null && this.uas !== undefined);
                                },
                                get akhir() {
                                    if (!this.hasInput) return null;
                                    const t = parseFloat(this.tugas || 0);
                                    const m = parseFloat(this.uts || 0);
                                    const u = parseFloat(this.uas || 0);
                                    return (((t * {{ $bobotTugas }}) + (m * {{ $bobotUts }}) + (u * {{ $bobotUas }})) / {{ $totalB }}).toFixed(2);
                                },
                                get grade() {
                                    if (this.akhir === null) return '-';
                                    const v = parseFloat(this.akhir);
                                    for (let p of this.preds) {
                                        if (v >= p.min && (p.max === null || v <= p.max)) {
                                            return p.grade;
                                        }
                                    }
                                    if (v >= 85) return 'A';
                                    if (v >= 75) return 'B';
                                    if (v >= 65) return 'C';
                                    if (v >= 50) return 'D';
                                    return 'E';
                                },
                                get badgeClass() {
                                    switch (this.grade) {
                                        case 'A': return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300';
                                        case 'B': return 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300';
                                        case 'C': return 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300';
                                        case 'D': return 'bg-orange-100 text-orange-800 dark:bg-orange-950 dark:text-orange-300';
                                        case 'E': return 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300';
                                        default:  return 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400';
                                    }
                                }
                            }">
                            <th scope="row" class="sticky left-0 z-[1] bg-white dark:bg-gray-800 px-5 py-4 whitespace-nowrap text-left font-normal border-b border-gray-100 dark:border-gray-700/60">
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $row['nama_lengkap'] }}</div>
                                <div class="text-xs text-gray-600 font-mono mt-0.5">{{ $row['nis'] }}</div>
                            </th>
                            <td class="px-3 py-4 whitespace-nowrap text-center border-b border-gray-100 dark:border-gray-700/60">
                                <input type="number" step="0.01" min="0" max="100"
                                       wire:model.defer="grades.{{ $santriId }}.nilai_tugas"
                                       x-model="tugas"
                                       placeholder="-"
                                       class="w-24 text-center px-2.5 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm font-semibold focus:ring-2 focus:ring-emerald-500">
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap text-center border-b border-gray-100 dark:border-gray-700/60">
                                <input type="number" step="0.01" min="0" max="100"
                                       wire:model.defer="grades.{{ $santriId }}.nilai_uts"
                                       x-model="uts"
                                       placeholder="-"
                                       class="w-24 text-center px-2.5 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm font-semibold focus:ring-2 focus:ring-emerald-500">
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap text-center border-b border-gray-100 dark:border-gray-700/60">
                                <input type="number" step="0.01" min="0" max="100"
                                       wire:model.defer="grades.{{ $santriId }}.nilai_uas"
                                       x-model="uas"
                                       placeholder="-"
                                       class="w-24 text-center px-2.5 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm font-semibold focus:ring-2 focus:ring-emerald-500">
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap text-center border-b border-gray-100 dark:border-gray-700/60">
                                <span class="font-bold text-gray-900 dark:text-gray-100 text-base" x-text="akhir !== null ? akhir : '{{ !empty($row['nilai_akhir']) ? $row['nilai_akhir'] : '-' }}'"></span>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap text-center border-b border-gray-100 dark:border-gray-700/60">
                                <span class="px-2.5 py-1 text-xs font-extrabold rounded" :class="badgeClass" x-text="grade"></span>
                            </td>
                            <td class="px-4 py-4 border-b border-gray-100 dark:border-gray-700/60">
                                <input type="text"
                                       wire:model.defer="grades.{{ $santriId }}.keterangan"
                                       placeholder="Catatan penguasaan materi / adab..."
                                       class="w-full px-3 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs focus:ring-2 focus:ring-emerald-500">
                            </td>
                        </tr>
                    @empty
                        @if(empty($lockedSantris))
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-gray-600 text-sm">
                                    Tidak ada santri aktif terdaftar di kelas ini.
                                </td>
                            </tr>
                        @endif
                    @endforelse

                    {{-- Baris Santri yang Terkunci / Approved --}}
                    @foreach($lockedSantris as $lockedId => $locked)
                        <tr class="bg-gray-50/70 dark:bg-gray-800/50 opacity-80" wire:key="locked-row-{{ $lockedId }}">
                            <th scope="row" class="sticky left-0 z-[1] bg-gray-50/90 dark:bg-gray-800/90 px-5 py-4 whitespace-nowrap text-left font-normal border-b border-gray-100 dark:border-gray-700/60">
                                <div class="font-semibold text-gray-700 dark:text-gray-300">{{ $locked['nama_lengkap'] }}</div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs text-gray-600 font-mono">{{ $locked['nis'] }}</span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800 shadow-sm">
                                        <svg class="w-3 h-3 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                        </svg>
                                        Terkunci (Approved)
                                    </span>
                                </div>
                            </th>
                            <td class="px-3 py-4 whitespace-nowrap text-center border-b border-gray-100 dark:border-gray-700/60 text-sm text-gray-600 font-mono">
                                {{ $locked['nilai_tugas'] }}
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap text-center border-b border-gray-100 dark:border-gray-700/60 text-sm text-gray-600 font-mono">
                                {{ $locked['nilai_uts'] }}
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap text-center border-b border-gray-100 dark:border-gray-700/60 text-sm text-gray-600 font-mono">
                                {{ $locked['nilai_uas'] }}
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap text-center border-b border-gray-100 dark:border-gray-700/60 text-base font-bold text-gray-600 dark:text-gray-400">
                                {{ $locked['nilai_akhir'] }}
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap text-center border-b border-gray-100 dark:border-gray-700/60">
                                <span class="px-2.5 py-1 text-xs font-bold rounded bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    {{ $locked['grade'] }}
                                </span>
                            </td>
                            <td class="px-4 py-4 border-b border-gray-100 dark:border-gray-700/60 text-xs text-gray-600 italic">
                                {{ $locked['keterangan'] ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if(!empty($grades))
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div class="text-xs text-gray-600 dark:text-gray-400">
                Penyimpanan memanfaatkan <strong>batch upsert</strong> (1 single atomic query) untuk performa instan tanpa N+1 overhead.
            </div>

            <div class="flex justify-end items-center gap-3">
                <a href="{{ route('teacher.grades.index') }}" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                    Batal
                </a>
                <button type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg transition text-sm font-semibold shadow-sm">
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Simpan &amp; Hitung Nilai Otomatis</span>
                </button>
            </div>
        </div>
    @elseif(!empty($lockedSantris))
        <div class="flex items-center gap-2 px-4 py-3 rounded-lg bg-amber-50 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs font-semibold">
            <svg class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
            </svg>
            Seluruh nilai santri pada jadwal ini telah disetujui (Approved) dan terkunci permanen.
        </div>
    @endif
</div>
