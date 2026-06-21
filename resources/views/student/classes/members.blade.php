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
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:65%;">Name</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <a href="{{ route('profile.view', $class->teacher->user_id) }}" class="d-flex align-items-center text-decoration-none text-reset">
                                    <div class="avatar avatar-sm me-3">
                                        @if ($class->teacher->avatar)
                                            <img src="{{ asset('storage/' . $class->teacher->avatar) }}" alt="Avatar" class="avatar-content rounded-circle" style="object-fit:cover;">
                                        @else
                                            <span class="avatar-content d-flex align-items-center justify-content-center rounded-circle bg-danger text-white fw-bold">
                                                {{ strtoupper(substr($class->teacher->full_name ?? '?', 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="font-bold">{{ $class->teacher->full_name }}</span>
                                </a>
                            </td>
                            <td><span class="badge bg-light-primary">Teacher</span></td>
                        </tr>
                        @forelse ($members as $enrollment)
                            <tr>
                                <td>
                                    <a href="{{ route('profile.view', $enrollment->student->user_id) }}" class="d-flex align-items-center text-decoration-none text-reset">
                                        <div class="avatar avatar-sm me-3">
                                            @if ($enrollment->student->avatar)
                                                <img src="{{ asset('storage/' . $enrollment->student->avatar) }}" alt="Avatar" class="avatar-content rounded-circle" style="object-fit:cover;">
                                            @else
                                                <span class="avatar-content d-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-bold">
                                                    {{ strtoupper(substr($enrollment->student->full_name ?? '?', 0, 1)) }}
                                                </span>
                                            @endif
                                        </div>
                                        <span class="font-bold">{{ $enrollment->student->full_name }}</span>
                                    </a>
                                </td>
                                <td><span class="badge bg-light-secondary">Student</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-muted">No other students have joined yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
