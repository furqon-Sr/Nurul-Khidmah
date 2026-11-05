@extends('layouts.admin')

@section('title', 'Input Presensi Santri')

@section('content')
    <livewire:teacher.batch-attendance-input :jadwal="$schedule ?? $jadwal" :tanggal="$tanggal" />
@endsection
