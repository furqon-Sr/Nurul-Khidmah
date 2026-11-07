{{-- Badge role untuk panel admin & portal ($user = model User) --}}
@if($user->isSuperAdmin())
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide shadow-sm bg-gradient-to-r from-amber-400 to-yellow-500 text-amber-950 ring-1 ring-amber-600/30">
        Super Admin
    </span>
@elseif($user->isKepalaSekolah())
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide shadow-sm bg-cyan-700 text-white ring-1 ring-cyan-800/40">
        Kepala Madrasah
    </span>
@elseif($user->isPimpinan())
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide shadow-sm bg-purple-600 text-white ring-1 ring-purple-700/40">
        Pimpinan Yayasan
    </span>
@elseif($user->isPengurusAkademik())
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide shadow-sm bg-emerald-600 text-white ring-1 ring-emerald-800/40">
        Staf TU / Akademik
    </span>
@elseif($user->isTenagaPengajar())
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide shadow-sm bg-indigo-600 text-white ring-1 ring-indigo-700/40">
        Guru / Wali Kelas
    </span>
@elseif($user->isSantri())
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide shadow-sm bg-teal-600 text-white ring-1 ring-teal-700/40">
        Wali Santri
    </span>
@else
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide shadow-sm bg-slate-600 text-white ring-1 ring-slate-700/40">
        {{ $user->roleLabel() }}
    </span>
@endif
