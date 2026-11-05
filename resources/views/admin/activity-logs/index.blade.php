@extends('layouts.admin')

@section('title', 'Log Aktivitas Admin')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Log Aktivitas Admin
        </h1>
    </div>
</div>

{{-- Filter --}}
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-5 mb-6">
    <form action="{{ route('admin.activity-logs.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Pencarian</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama, deskripsi, IP..."
                   class="w-full rounded-lg border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100 text-sm py-2">
        </div>
        <div class="min-w-[150px]">
            <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Jenis Aksi</label>
            <select name="action" class="w-full rounded-lg border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100 text-sm py-2">
                <option value="">-- Semua --</option>
                <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Login</option>
                <option value="logout" {{ request('action') === 'logout' ? 'selected' : '' }}>Logout</option>
                <option value="create" {{ request('action') === 'create' ? 'selected' : '' }}>Create</option>
                <option value="update" {{ request('action') === 'update' ? 'selected' : '' }}>Update</option>
                <option value="delete" {{ request('action') === 'delete' ? 'selected' : '' }}>Delete</option>
                <option value="export" {{ request('action') === 'export' ? 'selected' : '' }}>Export</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="px-5 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg transition shadow-sm font-semibold text-sm">
                Filter
            </button>
            @if(request()->anyFilled(['search', 'action']))
                <a href="{{ route('admin.activity-logs.index') }}" class="px-4 py-2 border border-gray-300 dark:border-slate-700 text-gray-700 dark:text-slate-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 transition text-sm">
                    Clear
                </a>
            @endif
        </div>
    </form>
</div>

{{-- Tabel Log --}}
<div class="bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
            <thead class="bg-gray-50 dark:bg-slate-900">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Waktu</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Admin</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Aksi</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">IP Address</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-100 dark:divide-slate-700">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-900/60 transition-colors">
                        <td class="px-5 py-3.5 whitespace-nowrap text-xs text-gray-500 dark:text-slate-400">
                            <div>{{ $log->created_at->timezone(config('app.timezone'))->format('d M Y') }}</div>
                            <div class="text-gray-400 dark:text-slate-500">{{ $log->created_at->timezone(config('app.timezone'))->format('H:i:s') }}</div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900 dark:text-slate-100">{{ $log->user_name }}</span>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide {{ $log->actionColor() }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-gray-700 dark:text-slate-300 max-w-md">
                            <span class="line-clamp-2">{{ $log->description }}</span>
                            @if($log->target_type)
                                <span class="text-xs text-gray-400 dark:text-slate-500 block mt-0.5">{{ $log->target_type }} #{{ $log->target_id }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-xs font-mono text-gray-500 dark:text-slate-400">
                            {{ $log->ip_address }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-base font-medium">Belum ada log aktivitas</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
