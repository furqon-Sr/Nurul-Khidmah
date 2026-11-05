@extends('layouts.admin')

@section('title', 'Data Pendaftar PPDB')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Daftar Santri Baru</h1>
        <p class="text-gray-600 dark:text-gray-400">Kelola dan tinjau data pendaftaran santri baru secara realtime.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-6 p-4 bg-green-100 dark:bg-green-950/60 border-l-4 border-green-500 text-green-700 dark:text-green-300 rounded shadow-sm text-sm">
    {{ session('success') }}
</div>
@endif

<livewire:admin.registration-table 
    :search="request('search', '')" 
    :status="request('status', '')" 
    :target_class="request('target_class', '')" 
    :academic_year="request('academic_year', '')" />
@endsection
