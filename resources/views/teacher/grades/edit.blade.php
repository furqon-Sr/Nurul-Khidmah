@extends('layouts.admin')

@section('title', 'Input Nilai Santri')

@section('content')
    <livewire:teacher.batch-grade-input :jadwal="$schedule ?? $jadwal" />
@endsection
