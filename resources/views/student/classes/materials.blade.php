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
                    <a href="{{ route('student.classes.materials.download', [$class->class_id, $material->material_id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-download me-1"></i> Download
                    </a>
                </div>
            @empty
                <p class="text-muted mb-0">No files have been shared yet.</p>
            @endforelse
        </div>
    </div>

@endsection
