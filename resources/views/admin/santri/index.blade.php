@extends('layouts.admin')

@section('title', 'Manajemen Data Santri')

@section('content')
    <livewire:admin.santri-table :search="request('search', '')" :kelas_id="request('kelas_id', '')" :status="request('status', '')" />
@endsection
