@extends('layouts.app')

@section('title', $class->class_name . ' — Members')

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

    <div class="card">
        <div class="card-header">
            <h4>Members</h4>
            <span class="badge bg-secondary">{{ $members->count() }}</span>
        </div>
        <div class="card-body">

            <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                <a href="{{ route('profile.show') }}" class="d-flex align-items-center text-decoration-none text-reset">
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
                        <p class="text-muted mb-0" style="font-size:12px;">Teacher (you)</p>
                    </div>
                </a>
            </div>

            @forelse ($members as $enrollment)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="{{ route('profile.view', $enrollment->student->user_id) }}" class="d-flex align-items-center text-decoration-none text-reset">
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
                            <p class="text-muted mb-0" style="font-size:12px;">{{ $enrollment->student->email }}</p>
                        </div>
                    </a>
                    <form action="{{ route('teacher.classes.members.kick', [$class->class_id, $enrollment->student->user_id]) }}" method="POST"
                          onsubmit="return confirm('Remove {{ $enrollment->student->full_name }} from this class?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-person-dash me-1"></i> Remove
                        </button>
                    </form>
                </div>
            @empty
                <p class="text-muted mb-0">No students have joined yet. Share the class code: <strong>{{ $class->class_code }}</strong></p>
            @endforelse
        </div>
    </div>

@endsection
