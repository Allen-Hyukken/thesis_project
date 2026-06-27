@extends('layouts.app')

@section('title', $class->class_name . ' — Files')

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
@endsection

@section('page-heading')
    @include('student.classes.partials.class-header')
@endsection

@section('content')

    @include('student.classes.partials.class-nav')

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Shared Files</h4>
            <span class="badge bg-secondary">{{ $class->materials->count() }}</span>
        </div>
        <div class="card-body">
            @include('student.classes.partials.file-list', ['role' => 'student'])
        </div>
    </div>

@endsection
