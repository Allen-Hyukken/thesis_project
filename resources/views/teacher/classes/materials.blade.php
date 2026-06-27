@extends('layouts.app')

@section('title', $class->class_name . ' — Files')

@section('sidebar-nav')
    @include('teacher.partials.sidebar-nav')
@endsection

@section('page-heading')
    @include('teacher.classes.partials.class-header')
@endsection

@section('content')

    @include('teacher.classes.partials.class-nav')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="row">
        {{-- Upload form --}}
        <div class="col-12 col-lg-4 mb-3">
            <div class="card sticky-top" style="top:20px;">
                <div class="card-header">
                    <h4 class="mb-0">Upload a File</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('teacher.classes.materials.store', $class->class_id) }}"
                          method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Title</label>
                            <input type="text" name="title" class="form-control"
                                   placeholder="e.g. Week 3 Reading" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">File</label>
                            <input type="file" name="file" class="form-control" required>
                            <p class="text-muted mt-1 mb-0" style="font-size:12px;">
                                Max 50MB. PDF, images, videos, Office docs supported.
                            </p>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload me-1"></i> Upload
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- File list --}}
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Shared Files</h4>
                    <span class="badge bg-secondary">{{ $class->materials->count() }}</span>
                </div>
                <div class="card-body">
                    @include('teacher.classes.partials.file-list', ['role' => 'teacher'])
                </div>
            </div>
        </div>
    </div>

@endsection
