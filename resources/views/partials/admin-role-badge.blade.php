{{-- Badge role untuk panel admin & portal ($user = model User) --}}
@if($user->isSuperAdmin())
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide shadow-sm bg-gradient-to-r from-amber-400 to-yellow-500 text-amber-950 ring-1 ring-amber-600/30">
        Super Admin
    </span>
@elseif($user->isTenagaPengajar())
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide shadow-sm bg-indigo-600 text-white ring-1 ring-indigo-700/40">
        Ustaz / Pengajar
    </span>
@elseif($user->isPimpinan())
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide shadow-sm bg-purple-600 text-white ring-1 ring-purple-700/40">
        Pimpinan Pesantren
    </span>
@elseif($user->isSantri())
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide shadow-sm bg-teal-600 text-white ring-1 ring-teal-700/40">
        Santri
    </span>
@elseif($user->role === 'pengurus_akademik')
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide shadow-sm bg-emerald-600 text-white ring-1 ring-emerald-700/40">
        Pengurus Akademik
    </span>
@else
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide shadow-sm bg-sky-600 text-white ring-1 ring-sky-700/40">
        Admin
    </span>
@endif
