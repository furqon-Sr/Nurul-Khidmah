{{-- =======================================================================
     1. NON-MODAL SIDE DRAWER: Edit Evaluasi Santri (<dialog popover="manual">)
     Pengguna tetap dapat melihat & mengklik daftar santri di tabel utama
     sambil mengedit predikat sikap & catatan wali kelas satu santri.
     ======================================================================= --}}
<dialog id="rapor-eval-drawer"
        popover="manual"
        aria-labelledby="rapor-eval-drawer-title"
        class="rapor-side-drawer bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-2xl border-l border-gray-200 dark:border-gray-800">
    {{-- Drawer Header --}}
    <div class="flex items-start justify-between gap-3 px-5 py-4 border-b border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-900">
        <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <h2 id="rapor-eval-drawer-title" class="text-base font-bold text-gray-900 dark:text-white">
                    Edit Evaluasi Santri
                </h2>
                <span id="drawer-status-badge" class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Draft
                </span>
            </div>
            <p id="drawer-santri-subtitle" class="mt-0.5 text-xs text-gray-600 dark:text-gray-400 truncate">
                Pilih santri pada tabel
            </p>
        </div>

        <div class="flex items-center gap-1 shrink-0">
            <button type="button" id="drawer-prev-btn"
                    title="Santri sebelumnya"
                    aria-label="Santri sebelumnya"
                    class="inline-flex size-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:opacity-40 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition cursor-pointer">
                <svg aria-hidden="true" class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" id="drawer-next-btn"
                    title="Santri berikutnya"
                    aria-label="Santri berikutnya"
                    class="inline-flex size-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:opacity-40 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition cursor-pointer">
                <svg aria-hidden="true" class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
            <button type="button" id="drawer-close-btn"
                    aria-label="Tutup panel evaluasi"
                    class="inline-flex size-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-200/70 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100 transition cursor-pointer">
                <svg aria-hidden="true" class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- Drawer Body --}}
    <div class="flex-1 overflow-y-auto p-5 space-y-4">
        {{-- Banner Catatan Revisi Resmi (tampil bila status = revision) --}}
        <div id="drawer-revision-alert" hidden class="rounded-xl border border-amber-200 bg-amber-50/90 p-3.5 text-xs text-amber-900 dark:border-amber-800/70 dark:bg-amber-950/40 dark:text-amber-200">
            <div class="font-bold flex items-center gap-1.5 mb-1">
                <svg aria-hidden="true" class="size-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                <span>Catatan Revisi Resmi</span>
            </div>
            <p id="drawer-revision-text" class="leading-relaxed"></p>
        </div>

        {{-- Banner Terkunci (tampil bila rapor sudah Disetujui / Terbit) --}}
        <div id="drawer-locked-alert" hidden class="rounded-xl border border-emerald-200 bg-emerald-50/80 p-3.5 text-xs text-emerald-900 dark:border-emerald-800/70 dark:bg-emerald-950/40 dark:text-emerald-200">
            <div class="font-bold mb-0.5">Dokumen Rapor Terkunci</div>
            <p class="leading-relaxed">Rapor santri ini telah disetujui secara resmi. Perubahan evaluasi hanya dapat dilakukan melalui prosedur <strong>Buka Revisi Resmi</strong> oleh Kepala Sekolah atau Pimpinan.</p>
        </div>

        {{-- Ringkasan Akademik Objektif Santri --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-800/40 p-3.5">
            <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">
                Referensi Akademik Periode Ini
            </div>
            <div class="grid grid-cols-3 gap-2.5 text-center">
                <div class="rounded-lg bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-700/80 px-2.5 py-2">
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">Mapel Dinilai</div>
                    <div id="drawer-ref-mapel" class="mt-0.5 text-sm font-bold text-gray-900 dark:text-white tabular-nums">0/0</div>
                </div>
                <div class="rounded-lg bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-700/80 px-2.5 py-2">
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">Rata-rata</div>
                    <div id="drawer-ref-avg" class="mt-0.5 text-sm font-bold text-gray-900 dark:text-white tabular-nums">—</div>
                </div>
                <div class="rounded-lg bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-700/80 px-2.5 py-2">
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">H / I / S / A</div>
                    <div id="drawer-ref-absen" class="mt-0.5 text-xs font-bold text-gray-900 dark:text-white tabular-nums">0/0/0/0</div>
                </div>
            </div>
            <div id="drawer-missing-box" hidden class="mt-2.5 pt-2.5 border-t border-gray-200 dark:border-gray-700/70 text-xs text-amber-700 dark:text-amber-300">
                <span class="font-semibold">Kekurangan saat ini:</span>
                <span id="drawer-missing-list"></span>
            </div>
        </div>

        {{-- Form Evaluasi Santri --}}
        <form id="drawer-eval-form" method="POST" action="" class="space-y-4">
            @csrf
            <input type="hidden" name="tahun_ajaran_id" id="drawer_tahun_ajaran_id" value="{{ $selectedYear?->id }}">
            <input type="hidden" name="tingkat" value="{{ $selectedTingkat }}">
            <input type="hidden" name="filter_kelas_id" value="{{ $selectedFilterKelas?->id }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="tab" id="drawer_active_tab" value="{{ $activeTab }}">
            <input type="hidden" name="save_mode" id="drawer_save_mode" value="draft">

            <div>
                <label for="drawer_nilai_sikap" class="block text-xs font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                    Predikat Sikap &amp; Kepesantrenan
                </label>
                <select id="drawer_nilai_sikap" name="nilai_sikap"
                        class="w-full min-h-11 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-950 text-base sm:text-sm font-medium text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20">
                    <option value="">Pilih Predikat</option>
                    @foreach(['Sangat Baik (A)', 'Baik (B)', 'Cukup (C)', 'Perlu Bimbingan (D)'] as $opt)
                        <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="flex items-center justify-between gap-2 mb-1.5">
                    <label for="drawer_catatan_wali" class="block text-xs font-semibold text-gray-800 dark:text-gray-200">
                        Catatan Wali Kelas
                    </label>
                    <span id="drawer-char-count" class="text-[11px] text-gray-500 dark:text-gray-400 tabular-nums">0 / 1000</span>
                </div>
                <textarea id="drawer_catatan_wali"
                          name="catatan_wali_kelas"
                          maxlength="1000"
                          aria-describedby="drawer_catatan_help"
                          placeholder="Tuliskan perkembangan adab, kedisiplinan ibadah berjamaah, capaian belajar, serta saran pembinaan untuk santri..."
                          class="rapor-eval-textarea px-3.5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-950 text-base sm:text-sm leading-relaxed text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"></textarea>
                <p id="drawer_catatan_help" class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                    Gunakan catatan yang objektif dan membangun.
                </p>
            </div>

            <div id="drawer-feedback" role="status" aria-live="polite" hidden
                 class="rounded-lg px-3 py-2 text-xs font-medium"></div>
        </form>
    </div>

    {{-- Drawer Footer --}}
    <div class="border-t border-gray-200 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-900 px-5 py-3.5 flex flex-wrap items-center justify-between gap-2">
        <a id="drawer-preview-link" href="#" target="_blank"
           class="inline-flex h-10 items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
            <svg aria-hidden="true" class="size-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <span>Pratinjau</span>
        </a>

        <div id="drawer-Unlocked-actions" class="flex items-center gap-2 ml-auto">
            <button type="button" id="drawer-save-draft-btn"
                    class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-3.5 text-xs font-semibold text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                Simpan Draft
            </button>
            <button type="button" id="drawer-save-validate-btn"
                    class="inline-flex h-10 items-center justify-center rounded-lg bg-primary-600 hover:bg-primary-700 px-4 text-xs font-semibold text-white shadow-xs transition cursor-pointer">
                Simpan &amp; Validasi
            </button>
        </div>
    </div>
</dialog>

{{-- =======================================================================
     2. MODAL KONFIRMASI REVIEW & APPROVAL PER SANTRI (<dialog> showModal())
     ======================================================================= --}}
<dialog id="approve-review-dialog"
        aria-labelledby="approve-review-title"
        class="m-auto w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-0 text-left shadow-xl backdrop:bg-gray-950/50 dark:border-gray-800 dark:bg-gray-900">
    <div class="p-6">
        <div class="flex items-start justify-between gap-3">
            <div>
                <span class="inline-flex items-center rounded-md bg-primary-50 px-2 py-0.5 text-[11px] font-semibold text-primary-700 dark:bg-primary-950/60 dark:text-primary-300">
                    Konfirmasi Persetujuan Rapor
                </span>
                <h2 id="approve-review-title" class="mt-1.5 text-base font-bold text-gray-900 dark:text-white">
                    Tinjau &amp; Setujui Rapor Santri
                </h2>
            </div>
        </div>

        <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/40 p-4 space-y-2.5 text-xs">
            <div class="flex justify-between gap-3">
                <span class="text-gray-500 dark:text-gray-400">Santri</span>
                <strong id="review-modal-santri" class="text-gray-900 dark:text-white text-right">-</strong>
            </div>
            <div class="flex justify-between gap-3">
                <span class="text-gray-500 dark:text-gray-400">Kelas &amp; Versi Dokumen</span>
                <strong id="review-modal-kelas" class="text-gray-900 dark:text-white text-right">-</strong>
            </div>
            <div class="flex justify-between gap-3">
                <span class="text-gray-500 dark:text-gray-400">Kelengkapan Nilai Akademik</span>
                <strong id="review-modal-nilai" class="text-gray-900 dark:text-white text-right">-</strong>
            </div>
            <div class="flex justify-between gap-3">
                <span class="text-gray-500 dark:text-gray-400">Predikat Sikap</span>
                <strong id="review-modal-sikap" class="text-gray-900 dark:text-white text-right">-</strong>
            </div>
            <div class="pt-2 border-t border-gray-200 dark:border-gray-700/80">
                <div class="text-gray-500 dark:text-gray-400 mb-1">Catatan Wali Kelas:</div>
                <p id="review-modal-catatan" class="text-gray-800 dark:text-gray-200 leading-relaxed italic line-clamp-3">-</p>
            </div>
        </div>

        <form id="approve-review-form" method="POST" action="" class="mt-4">
            @csrf
            <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYear?->id }}">
            <input type="hidden" name="enforce_validation" value="1">

            <label class="flex items-start gap-2.5 rounded-xl border border-gray-200 dark:border-gray-800 p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/60 transition">
                <input type="checkbox" name="auto_publish" value="1" checked
                       class="mt-0.5 size-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900">
                <span class="text-xs leading-snug text-gray-700 dark:text-gray-300">
                    <strong class="block text-gray-900 dark:text-white">Terbitkan dokumen resmi sekaligus</strong>
                    Rapor langsung berstatus <strong>Terbit</strong> dengan stempel waktu penerbitan dan nomor versi resmi. Nilai &amp; presensi dikunci dari perubahan bebas.
                </span>
            </label>

            <div class="mt-5 flex items-center justify-end gap-2.5">
                <button type="button" data-close-dialog="approve-review-dialog" autofocus
                        class="h-10 rounded-lg border border-gray-200 bg-white px-4 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="h-10 rounded-lg bg-primary-600 hover:bg-primary-700 px-4 text-xs font-semibold text-white shadow-xs transition cursor-pointer">
                    Setujui Rapor
                </button>
            </div>
        </form>
    </div>
</dialog>

{{-- =======================================================================
     3. MODAL KONFIRMASI PERSETUJUAN KOLEKTIF KELAS (<dialog> showModal())
     ======================================================================= --}}
@if($selectedEvalKelas && (auth()->user()->isKepalaSekolah() || auth()->user()->isPimpinan() || auth()->user()->isSuperAdmin()))
<dialog id="batch-approve-dialog"
        aria-labelledby="batch-approve-title"
        class="m-auto w-full max-w-md rounded-2xl border border-gray-200 bg-white p-0 text-left shadow-xl backdrop:bg-gray-950/50 dark:border-gray-800 dark:bg-gray-900">
    <div class="p-6">
        <h2 id="batch-approve-title" class="text-base font-bold text-gray-900 dark:text-white">
            Setujui Rapor Siap Setuju — {{ $selectedEvalKelas->nama_kelas }}
        </h2>
        <p class="mt-2 text-xs leading-relaxed text-gray-600 dark:text-gray-400">
            Sistem hanya akan menyetujui santri yang telah memenuhi seluruh syarat wajib (nilai mata pelajaran lengkap, predikat sikap terisi, dan catatan wali kelas terisi).
        </p>

        <div class="mt-3.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 p-3.5 text-xs space-y-1.5">
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Memenuhi syarat persetujuan:</span>
                <strong class="text-emerald-700 dark:text-emerald-300">{{ $workflowSummary['ready_approve'] + $workflowSummary['ready_review'] }} santri</strong>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Belum lengkap (dilewati otomatis):</span>
                <strong class="text-amber-700 dark:text-amber-300">{{ $workflowSummary['draft'] + $workflowSummary['incomplete'] + $workflowSummary['revision'] }} santri</strong>
            </div>
        </div>

        <form action="{{ route('admin.reports.approve-class', $selectedEvalKelas->id) }}" method="POST" class="mt-4">
            @csrf
            <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYear?->id }}">
            <input type="hidden" name="enforce_validation" value="1">

            <label class="flex items-start gap-2.5 rounded-xl border border-gray-200 dark:border-gray-800 p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/60 transition">
                <input type="checkbox" name="auto_publish" value="1" checked
                       class="mt-0.5 size-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900">
                <span class="text-xs leading-snug text-gray-700 dark:text-gray-300">
                    <strong class="block text-gray-900 dark:text-white">Terbitkan dokumen resmi sekaligus</strong>
                    Tandai langsung sebagai <strong>Terbit</strong> beserta waktu penerbitan resmi.
                </span>
            </label>

            <div class="mt-5 flex items-center justify-end gap-2.5">
                <button type="button" data-close-dialog="batch-approve-dialog" autofocus
                        class="h-10 rounded-lg border border-gray-200 bg-white px-4 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="h-10 rounded-lg bg-primary-600 hover:bg-primary-700 px-4 text-xs font-semibold text-white shadow-xs transition cursor-pointer">
                    Konfirmasi Setujui Kelas
                </button>
            </div>
        </form>
    </div>
</dialog>
@endif

{{-- =======================================================================
     4. MODAL LIHAT KEKURANGAN / DETAIL PERSETUJUAN / CATATAN REVISI
     ======================================================================= --}}
<dialog id="detail-status-dialog"
        aria-labelledby="detail-status-title"
        class="m-auto w-full max-w-md rounded-2xl border border-gray-200 bg-white p-0 text-left shadow-xl backdrop:bg-gray-950/50 dark:border-gray-800 dark:bg-gray-900">
    <div class="p-6">
        <h2 id="detail-status-title" class="text-base font-bold text-gray-900 dark:text-white">
            Detail Status Rapor
        </h2>
        <p id="detail-status-subtitle" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400"></p>

        <div id="detail-status-body" class="mt-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/40 p-4 text-xs text-gray-700 dark:text-gray-200 space-y-2 leading-relaxed">
        </div>

        <div class="mt-5 flex items-center justify-end">
            <form method="dialog">
                <button type="submit" value="close" autofocus
                        class="h-10 rounded-lg border border-gray-200 bg-white px-4 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">
                    Tutup
                </button>
            </form>
        </div>
    </div>
</dialog>

{{-- =======================================================================
     5. MODAL PROSEDUR REVISI RESMI (<dialog> showModal())
     ======================================================================= --}}
@if(auth()->user()->isKepalaSekolah() || auth()->user()->isPimpinan() || auth()->user()->isSuperAdmin())
<dialog id="revision-request-dialog"
        aria-labelledby="revision-request-title"
        class="m-auto w-full max-w-md rounded-2xl border border-gray-200 bg-white p-0 text-left shadow-xl backdrop:bg-gray-950/50 dark:border-gray-800 dark:bg-gray-900">
    <div class="p-6">
        <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
            Prosedur Revisi Resmi
        </span>
        <h2 id="revision-request-title" class="mt-1.5 text-base font-bold text-gray-900 dark:text-white">
            Buka Revisi Dokumen Rapor
        </h2>
        <p id="revision-request-subtitle" class="mt-1 text-xs leading-relaxed text-gray-600 dark:text-gray-400">
            Membuka kunci rapor untuk perbaikan terukur. Saat disetujui kembali, nomor versi dokumen PDF akan bertambah otomatis dan tercatat di log audit.
        </p>

        <form id="revision-request-form" method="POST" action="" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYear?->id }}">

            <div>
                <label for="revision_note_input" class="block text-xs font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                    Alasan &amp; Catatan Revisi <span class="text-error-600">*</span>
                </label>
                <textarea id="revision_note_input"
                          name="revision_note"
                          required
                          minlength="5"
                          maxlength="1000"
                          rows="3"
                          placeholder="Contoh: Koreksi nilai UAS Fiqih berdasarkan berita acara susulan & penyesuaian catatan wali kelas..."
                          class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-950 text-base sm:text-xs text-gray-900 dark:text-gray-100 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2.5">
                <button type="button" data-close-dialog="revision-request-dialog" autofocus
                        class="h-10 rounded-lg border border-gray-200 bg-white px-4 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="h-10 rounded-lg bg-amber-600 hover:bg-amber-700 px-4 text-xs font-semibold text-white shadow-xs transition cursor-pointer">
                    Buka Revisi Resmi
                </button>
            </div>
        </form>
    </div>
</dialog>
@endif
