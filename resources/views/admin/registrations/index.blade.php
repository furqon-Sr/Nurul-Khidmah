@extends('layouts.admin')

@section('title', 'Data Pendaftar PPDB')

@section('content')
<div class="mb-5 flex flex-col gap-1">
    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Daftar Pendaftar PPDB</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400">Kelola, tinjau, dan verifikasi data pendaftaran santri baru secara realtime.</p>
</div>

@if(session('success'))
<div role="status" class="mb-5 flex items-center gap-2.5 rounded-xl border border-success-200 bg-success-50/80 px-4 py-3 text-sm font-medium text-success-800 dark:border-success-800/60 dark:bg-success-950/40 dark:text-success-300">
    <svg class="size-4 shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    <span>{{ session('success') }}</span>
</div>
@endif

<livewire:admin.registration-table 
    :search="request('search', '')" 
    :status="request('status', '')" 
    :target_class="request('target_class', '')" 
    :academic_year="request('academic_year', '')" />
@endsection
