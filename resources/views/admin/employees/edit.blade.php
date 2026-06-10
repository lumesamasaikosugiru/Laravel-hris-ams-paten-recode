@extends('layouts.admin')
@section('title','Edit Data Pegawai')
@section('subtitle', $employee->name)
@section('content')
    @livewire('admin.employee-form', ['employeeId' => $employee->id])
@endsection
