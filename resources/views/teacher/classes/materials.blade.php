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
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="row">
        <div class="col-12 col-lg-5 mb-3">
            <div class="card">
                <div class="card-header">
                    <h4>Upload a File</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('teacher.classes.materials.store', $class->class_id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label font-bold">Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Week 3 Reading" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-bold">File</label>
                            <input type="file" name="file" class="form-control" required>
                            <p class="text-muted mt-1 mb-0" style="font-size:12px;">Max 50MB. Any file type.</p>
                        </div>
                        <button type="submit" class="btn btn-primary font-bold w-100">
                            <i class="bi bi-upload me-1"></i> Upload
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h4>Shared Files</h4>
                    <span class="badge bg-secondary">{{ $class->materials->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse ($class->materials as $material)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md me-3">
                                    <span class="d-flex align-items-center justify-content-center w-100 h-100 rounded bg-light text-primary fs-5">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </span>
                                </div>
                                <div>
                                    <p class="font-bold mb-0">{{ $material->title }}</p>
                                    <p class="text-muted mb-0" style="font-size:12px;">
                                        {{ $material->original_filename }} • {{ $material->humanSize() }}
                                    </p>
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('teacher.classes.materials.download', [$class->class_id, $material->material_id]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i>
                                </a>
                                <form action="{{ route('teacher.classes.materials.destroy', [$class->class_id, $material->material_id]) }}" method="POST"
                                      onsubmit="return confirm('Delete this file?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No files uploaded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

@endsection
