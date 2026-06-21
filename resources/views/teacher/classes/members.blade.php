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
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:55%;">Name</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <a href="{{ route('profile.show') }}" class="d-flex align-items-center text-decoration-none text-reset">
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
                            <td><span class="badge bg-light-primary">Teacher (you)</span></td>
                            <td class="text-muted">{{ $class->teacher->email }}</td>
                            <td></td>
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
                                <td class="text-muted">{{ $enrollment->student->email }}</td>
                                <td class="text-end">
                                    <form action="{{ route('teacher.classes.members.kick', [$class->class_id, $enrollment->student->user_id]) }}" method="POST"
                                          onsubmit="return confirm('Remove {{ $enrollment->student->full_name }} from this class?');" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-person-dash me-1"></i> Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-muted">No students have joined yet. Share the class code: <strong>{{ $class->class_code }}</strong></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
