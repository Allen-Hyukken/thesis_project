@extends('layouts.app')

@section('title', 'My Classes')

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
@endsection

@section('page-heading')
    <div class="d-flex justify-content-between align-items-center">
        <h3>My Classes</h3>
        <button type="button" class="btn btn-primary font-bold" data-bs-toggle="modal" data-bs-target="#joinClassModal">
            <i class="bi bi-plus-circle me-1"></i> Join a Class
        </button>
    </div>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        @forelse ($classes as $class)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="font-bold mb-1">{{ $class->class_name }}</h5>
                        <p class="text-muted mb-2" style="font-size:13px;">{{ $class->subject }}</p>
                        <p class="mb-3" style="font-size:13px;">
                            <i class="bi bi-person-video3 me-1"></i>
                            {{ $class->teacher->full_name ?? 'Unknown teacher' }}
                        </p>
                        <a href="{{ route('student.classes.show', $class->class_id) }}" class="btn btn-outline-primary btn-sm w-100 font-bold">
                            Open Class
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-journal-plus fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="font-bold">You haven't joined any classes yet</h5>
                        <p class="text-muted mb-0">Ask your teacher for a class code and join above.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @include('student.partials.join-class-modal')

@endsection
