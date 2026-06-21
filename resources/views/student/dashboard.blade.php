@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
@endsection

@section('page-heading')
    <h3>Student Dashboard</h3>
@endsection

@section('content')
    <section class="row">
        <div class="col-12 col-lg-9">

            {{-- Stat Cards --}}
            <div class="row">
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-3 py-4-5">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="stats-icon blue">
                                        <i class="bi bi-mortarboard-fill"></i>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Enrolled Classes</h6>
                                    <h6 class="font-extrabold mb-0">{{ $enrolledClassesCount }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-3 py-4-5">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="stats-icon green">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Avg. Score</h6>
                                    <h6 class="font-extrabold mb-0">{{ $avgScore !== null ? $avgScore . '%' : '—' }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-3 py-4-5">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="stats-icon purple">
                                        <i class="bi bi-list-task"></i>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Pending Tasks</h6>
                                    <h6 class="font-extrabold mb-0">{{ $pendingCount }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-3 py-4-5">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="stats-icon red">
                                        <i class="bi bi-trophy-fill"></i>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Completed</h6>
                                    <h6 class="font-extrabold mb-0">{{ $completedCount }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- My Classes --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>My Classes</h4>
                            <a href="{{ route('student.classes') }}" class="btn btn-primary btn-sm">View All</a>
                        </div>
                        <div class="card-body">
                            @forelse ($classes as $class)
                                <div class="d-flex align-items-center mb-4">
                                    <div class="avatar avatar-lg me-3">
                                        <div class="avatar-content d-flex align-items-center justify-content-center rounded bg-light">
                                            <i class="bi bi-journal-code fs-4" style="color:#435ebe;"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 font-bold">{{ $class->class_name }}</h6>
                                        <p class="text-muted mb-0" style="font-size:12px;">
                                            Instructor: {{ $class->teacher->full_name ?? 'Unknown' }}
                                            • {{ $class->posted_courses_count }} course{{ $class->posted_courses_count === 1 ? '' : 's' }}
                                        </p>
                                    </div>
                                    <div class="ms-3">
                                        <a href="{{ route('student.classes.show', $class->class_id) }}" class="btn btn-sm btn-outline-primary">Open</a>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted mb-0">
                                    You haven't joined any classes yet.
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#joinClassModal">Join one with a class code.</a>
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column --}}
        <div class="col-12 col-lg-3">

            {{-- Welcome Card --}}
            <div class="card">
                <div class="card-body py-4 px-4">
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('profile.show') }}" class="avatar avatar-xl me-3 text-decoration-none">
                            @if (auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar"
                                     class="rounded-circle" style="object-fit:cover;">
                            @else
                                <span class="avatar-content d-flex align-items-center justify-content-center rounded-circle bg-danger text-white fw-bold fs-4">
                                    {{ strtoupper(substr(auth()->user()->full_name ?? 'S', 0, 1)) }}
                                </span>
                            @endif
                        </a>
                        <div>
                            <h5 class="font-bold mb-0">{{ auth()->user()->full_name ?? 'Student' }}</h5>
                            <h6 class="text-muted mb-0">Student</h6>
                        </div>
                    </div>
                    <p class="text-muted mb-0" style="font-size:13px;">
                        Welcome back! Ready to continue your AI-powered learning journey?
                    </p>
                    <button type="button" class="btn btn-primary btn-block w-100 mt-3 font-bold"
                            data-bs-toggle="modal" data-bs-target="#joinClassModal">
                        <i class="bi bi-plus-circle me-1"></i> Join a Class
                    </button>
                </div>
            </div>

            {{-- Pending Tasks --}}
            <div class="card">
                <div class="card-header">
                    <h4>Pending Tasks</h4>
                    <span class="badge bg-warning text-dark">{{ $pendingCount }}</span>
                </div>
                <div class="card-content pb-4">
                    @forelse ($pendingTasks as $task)
                        <a href="{{ $task['link'] ?? ($task['class_id'] ? route('student.classes.show', $task['class_id']) : '#') }}"
                           class="recent-message d-flex px-4 py-3 text-decoration-none text-reset">
                            <div class="avatar avatar-md">
                                <span class="avatar-content d-flex align-items-center justify-content-center rounded-circle"
                                      style="background:#eef1fb;color:#435ebe;">
                                    <i class="bi {{ $task['icon'] }}"></i>
                                </span>
                            </div>
                            <div class="name ms-4 flex-grow-1">
                                <h5 class="mb-1" style="font-size:14px;">{{ $task['title'] }}</h5>
                                <h6 class="text-muted mb-0" style="font-size:12px;">
                                    {{ $task['type'] }} • {{ $task['course'] }}
                                    @if ($task['due_at'])
                                        • Due {{ $task['due_at']->format('M j') }}
                                    @endif
                                </h6>
                            </div>
                        </a>
                    @empty
                        <p class="text-muted px-4" style="font-size:13px;">You're all caught up — nothing pending right now.</p>
                    @endforelse
                </div>
            </div>

            {{-- AI Tutor --}}
            <div class="card">
                <div class="card-header">
                    <h4>Ask AI Tutor</h4>
                </div>
                <div class="card-content pb-4">
                    <div class="px-4 py-3">
                        <div class="p-3 rounded mb-3"
                             style="background:#f0f4ff;font-size:13px;color:#25396f;border-radius:0 12px 12px 12px!important;">
                            Hi! I'm your TUP AI Tutor. Ask me anything about your courses!
                        </div>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" placeholder="What is a Linked List?">
                            <button class="btn btn-primary">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    @include('student.partials.join-class-modal')
@endsection
