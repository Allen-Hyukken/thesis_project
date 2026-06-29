@extends('layouts.app')

@section('title', 'Teacher Dashboard')

@section('sidebar-nav')
    @include('teacher.partials.sidebar-nav')
@endsection

@section('page-heading')
    <h3>Teacher Dashboard</h3>
@endsection

@section('content')
    <section class="row">
        <div class="col-12 col-lg-9">

            {{-- Stat Cards --}}
            <div class="row">
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-3 py-4-5">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stats-icon blue flex-shrink-0">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <h6 class="text-muted font-semibold stat-label mb-1">Active Classes</h6>
                                    <h6 class="font-extrabold mb-0">{{ $activeClassesCount }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-3 py-4-5">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stats-icon green flex-shrink-0">
                                    <i class="bi bi-journal-bookmark-fill"></i>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <h6 class="text-muted font-semibold stat-label mb-1">Courses Published</h6>
                                    <h6 class="font-extrabold mb-0">{{ $publishedCoursesCount }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-3 py-4-5">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stats-icon purple flex-shrink-0">
                                    <i class="bi bi-person-badge-fill"></i>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <h6 class="text-muted font-semibold stat-label mb-1">Total Students</h6>
                                    <h6 class="font-extrabold mb-0">{{ $totalStudents }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-3 py-4-5">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stats-icon red flex-shrink-0">
                                    <i class="bi bi-pencil-square"></i>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <h6 class="text-muted font-semibold stat-label mb-1">Needs Grading</h6>
                                    <h6 class="font-extrabold mb-0">{{ $needsGradingCount }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- My Classes Table --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>My Classes</h4>
                            <a href="{{ route('teacher.classes') }}" class="btn btn-primary btn-sm">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-lg">
                                    <thead>
                                    <tr>
                                        <th>Class</th>
                                        <th>Students</th>
                                        <th>Courses</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($classes as $class)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <p class="font-bold mb-0 ms-3">{{ $class->class_name }}</p>
                                                </div>
                                                <small class="text-muted ms-3">{{ $class->subject }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-person-fill text-muted me-1"></i>
                                                    <p class="mb-0">{{ $class->enrollments_count }}</p>
                                                </div>
                                            </td>
                                            <td>{{ $class->posted_courses_count }}</td>
                                            <td>
                                                @if($class->is_active)
                                                    <span class="badge bg-light-success">Active</span>
                                                @else
                                                    <span class="badge bg-light-warning">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('teacher.classes.show', $class->class_id) }}" class="btn btn-sm btn-outline-primary">Manage</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No classes yet. <a href="{{ route('teacher.classes') }}">Create one</a> to get started.
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Student Activity --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Recent Student Activity</h4>
                            <a href="{{ route('teacher.results') }}" class="btn btn-primary btn-sm">Analytics</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-lg">
                                    <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Course</th>
                                        <th>Activity</th>
                                        <th>Score</th>
                                        <th>Time</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($recentActivity as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-md">
                                                    <span class="avatar-content d-flex align-items-center justify-content-center rounded-circle bg-light fw-bold" style="color:#435ebe;">
                                                        {{ strtoupper(substr($item['student'], 0, 1)) }}
                                                    </span>
                                                    </div>
                                                    <p class="font-bold ms-3 mb-0">{{ $item['student'] }}</p>
                                                </div>
                                            </td>
                                            <td class="text-muted">{{ $item['course'] }}</td>
                                            <td><i class="bi {{ $item['icon'] }} me-1"></i>{{ $item['action'] }}: {{ $item['title'] }}</td>
                                            <td><p class="font-bold mb-0">{{ $item['score'] }}</p></td>
                                            <td class="text-muted">{{ $item['when']?->diffForHumans() }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No student activity yet. Post a course to a class to get started.
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column --}}
        <div class="col-12 col-lg-3">

            {{-- Teacher Info --}}
            <div class="card">
                <div class="card-body py-4 px-4">
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('profile.show') }}" class="avatar avatar-xl me-3 text-decoration-none">
                            @if (auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar }}" alt="Avatar"
                                     class="rounded-circle" style="object-fit:cover;">
                            @else
                                <span class="avatar-content d-flex align-items-center justify-content-center rounded-circle bg-danger text-white fw-bold fs-4">
                                    {{ strtoupper(substr(auth()->user()->full_name ?? 'T', 0, 1)) }}
                                </span>
                            @endif
                        </a>
                        <div>
                            <h5 class="font-bold mb-0">{{ auth()->user()->full_name ?? 'Teacher' }}</h5>
                            <h6 class="text-muted mb-0">Instructor</h6>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('teacher.ai-generate') }}" class="btn btn-primary btn-sm w-100 font-bold">
                            <i class="bi bi-stars me-1"></i> AI Builder
                        </a>
                        <button type="button" class="btn btn-outline-primary btn-sm w-100 font-bold"
                                data-bs-toggle="modal" data-bs-target="#createClassModal">
                            <i class="bi bi-plus-lg me-1"></i> New Class
                        </button>
                    </div>
                </div>
            </div>

            {{-- AI Course Builder CTA --}}
            <div class="card" style="background:linear-gradient(135deg,#435ebe 0%,#6f42c1 100%);color:#fff;">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-stars fs-5"></i>
                        <span class="font-bold">AI Course Builder</span>
                    </div>
                    <p style="font-size:13px;opacity:.85;margin-bottom:16px;">
                        Describe a topic and let EDITH draft a course outline, lessons, activities, quizzes, and exams — you review and edit everything before it's saved.
                    </p>
                    <a href="{{ route('teacher.ai-generate') }}"
                       class="btn btn-block btn-xl btn-light font-bold w-100">
                        <i class="bi bi-lightning-charge-fill text-primary me-1"></i> Generate Course
                    </a>
                </div>
            </div>

            {{-- Needs Grading --}}
            <div class="card">
                <div class="card-header">
                    <h4>Needs Grading</h4>
                    <span class="badge bg-warning text-dark">{{ $needsGradingCount }}</span>
                </div>
                <div class="card-content pb-4">
                    @forelse ($needsGrading as $item)
                        <a href="{{ $item['class_id'] ? route('teacher.classes.gradebook', $item['class_id']) : '#' }}"
                           class="recent-message d-flex px-4 py-3 text-decoration-none text-reset">
                            <div class="avatar avatar-md">
                        <span class="avatar-content d-flex align-items-center justify-content-center rounded-circle"
                              style="background:#fef3dc;color:#f39c12;">
                            <i class="bi {{ $item['icon'] }}"></i>
                        </span>
                            </div>
                            <div class="name ms-4 flex-grow-1">
                                <h5 class="mb-1" style="font-size:14px;">{{ $item['title'] }}</h5>
                                <h6 class="text-muted mb-0" style="font-size:12px;">{{ $item['student'] }} • {{ $item['course'] }}</h6>
                            </div>
                            <span class="badge bg-warning text-dark align-self-center">Grade</span>
                        </a>
                    @empty
                        <p class="text-muted px-4" style="font-size:13px;">Nothing needs grading right now.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </section>

    @include('teacher.partials.create-class-modal')
@endsection
