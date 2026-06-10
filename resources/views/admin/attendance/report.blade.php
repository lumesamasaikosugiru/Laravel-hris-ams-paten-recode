@extends('layouts.admin')
@section('title','Laporan Absensi')
@section('subtitle','Rekap kehadiran bulanan per pegawai')
@section('content')
    @livewire('admin.attendance-report')
@endsection
