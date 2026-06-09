@extends('layouts.public')
@section('title', 'Lamar — ' . $job->title)

@section('content')
    @livewire('public.apply-form', ['job' => $job])
@endsection
