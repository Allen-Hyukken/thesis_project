@extends('layouts.app')

@section('title', 'My Classes')

@section('sidebar-nav')
    @include('teacher.partials.sidebar-nav')
@endsection

@section('page-heading')
    <div class="d-flex justify-content-between align-items-center">
        <h3>My Classes</h3>
        <button type="button" class="btn btn-primary font-bold" data-bs-toggle="modal" data-bs-target="#createClassModal">
            <i class="bi bi-plus-circle me-1"></i> Create Class
        </button>
    </div>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
            @if (session('new_class_code'))
                <br>
                Class code: <strong class="fs-5">{{ session('new_class_code') }}</strong>
                — share this with your students so they can join.
            @endif
        </div>
    @endif

    <div class="row">
        @forelse ($classes as $class)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="font-bold mb-0">{{ $class->class_name }}</h5>
                            <span class="badge {{ $class->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $class->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <p class="text-muted mb-2" style="font-size:13px;">{{ $class->subject }}</p>

                        @if ($class->description)
                            <p class="mb-3" style="font-size:13px;">{{ \Illuminate\Support\Str::limit($class->description, 90) }}</p>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-light text-dark border" style="font-size:13px;letter-spacing:1px;">
                                {{ $class->class_code }}
                            </span>
                            <span class="text-muted" style="font-size:12px;">
                                <i class="bi bi-people"></i>
                                {{ $class->enrollments_count }} student{{ $class->enrollments_count === 1 ? '' : 's' }}
                            </span>
                        </div>

                        <a href="{{ route('teacher.classes.show', $class->class_id) }}" class="btn btn-outline-primary btn-sm w-100 font-bold">
                            Open Class
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-mortarboard fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="font-bold">No classes yet</h5>
                        <p class="text-muted mb-0">Create your first class to get a class code your students can use to join.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @include('teacher.partials.create-class-modal')

@endsection
