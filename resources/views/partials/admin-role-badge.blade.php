{{-- Badge role untuk panel admin ($user = model User) --}}
@if($user->isSuperAdmin())
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide shadow-sm bg-gradient-to-r from-amber-400 to-yellow-500 text-amber-950 ring-1 ring-amber-600/30 dark:from-amber-500 dark:to-yellow-400 dark:text-amber-950">
        Super Admin
    </span>
@else
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide shadow-sm bg-sky-600 text-white ring-1 ring-sky-700/40 dark:bg-sky-500 dark:text-white">
        Admin
    </span>
@endif
