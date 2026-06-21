@extends('layouts.app')

@section('title', $class->class_name . ' — Members')

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
            <h4>Members</h4>
            <span class="badge bg-secondary">{{ $members->count() + 1 }}</span>
        </div>
        <div class="card-body">

            <a href="{{ route('profile.view', $class->teacher->user_id) }}" class="d-flex align-items-center text-decoration-none text-reset mb-3 pb-3 border-bottom">
                <div class="avatar avatar-md me-3">
                    @if ($class->teacher->avatar)
                        <img src="{{ asset('storage/' . $class->teacher->avatar) }}" alt="Avatar"
                             class="rounded-circle w-100 h-100" style="object-fit:cover;">
                    @else
                        <span class="d-flex align-items-center justify-content-center w-100 h-100 rounded-circle bg-danger text-white fw-bold">
                            {{ strtoupper(substr($class->teacher->full_name ?? '?', 0, 1)) }}
                        </span>
                    @endif
                </div>
                <div>
                    <p class="font-bold mb-0">{{ $class->teacher->full_name }}</p>
                    <p class="text-muted mb-0" style="font-size:12px;">Teacher</p>
                </div>
            </a>

            @forelse ($members as $enrollment)
                <a href="{{ route('profile.view', $enrollment->student->user_id) }}" class="d-flex align-items-center text-decoration-none text-reset mb-3">
                    <div class="avatar avatar-md me-3">
                        @if ($enrollment->student->avatar)
                            <img src="{{ asset('storage/' . $enrollment->student->avatar) }}" alt="Avatar"
                                 class="rounded-circle w-100 h-100" style="object-fit:cover;">
                        @else
                            <span class="d-flex align-items-center justify-content-center w-100 h-100 rounded-circle bg-primary text-white fw-bold">
                                {{ strtoupper(substr($enrollment->student->full_name ?? '?', 0, 1)) }}
                            </span>
                        @endif
                    </div>
                    <div>
                        <p class="font-bold mb-0">{{ $enrollment->student->full_name }}</p>
                        <p class="text-muted mb-0" style="font-size:12px;">Student</p>
                    </div>
                </a>
            @empty
                <p class="text-muted mb-0">No other students have joined yet.</p>
            @endforelse
        </div>
    </div>

@endsection
