@extends('layouts.app')

@section('title', 'Teacher Dashboard')

@section('sidebar-nav')
    <li class="sidebar-title">Main</li>

    <li class="sidebar-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
        <a href="{{ route('teacher.dashboard') }}" class="sidebar-link">
            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <li class="sidebar-title">Teaching</li>

    <li class="sidebar-item {{ request()->routeIs('teacher.classes') ? 'active' : '' }}">
        <a href="{{ route('teacher.classes') }}" class="sidebar-link">
            <i class="bi bi-people-fill"></i>
            <span>My Classes</span>
        </a>
    </li>

    <li class="sidebar-item {{ request()->routeIs('teacher.courses') ? 'active' : '' }}">
        <a href="{{ route('teacher.courses') }}" class="sidebar-link">
            <i class="bi bi-journal-bookmark-fill"></i>
            <span>Courses</span>
        </a>
    </li>

    <li class="sidebar-item {{ request()->routeIs('teacher.materials') ? 'active' : '' }}">
        <a href="{{ route('teacher.materials') }}" class="sidebar-link">
            <i class="bi bi-folder-fill"></i>
            <span>Learning Materials</span>
        </a>
    </li>

    <li class="sidebar-item {{ request()->routeIs('teacher.students') ? 'active' : '' }}">
        <a href="{{ route('teacher.students') }}" class="sidebar-link">
            <i class="bi bi-person-badge-fill"></i>
            <span>Students</span>
        </a>
    </li>

    <li class="sidebar-title">AI Tools</li>

    <li class="sidebar-item {{ request()->routeIs('teacher.ai-generate') ? 'active' : '' }}">
        <a href="{{ route('teacher.ai-generate') }}" class="sidebar-link">
            <i class="bi bi-stars"></i>
            <span>AI Course Builder</span>
        </a>
    </li>

    <li class="sidebar-item {{ request()->routeIs('teacher.results') ? 'active' : '' }}">
        <a href="{{ route('teacher.results') }}" class="sidebar-link">
            <i class="bi bi-bar-chart-fill"></i>
            <span>Results & Analytics</span>
        </a>
    </li>

    <li class="sidebar-title">Account</li>

    <li class="sidebar-item {{ request()->routeIs('teacher.profile') ? 'active' : '' }}">
        <a href="{{ route('teacher.profile') }}" class="sidebar-link">
            <i class="bi bi-person-circle"></i>
            <span>My Profile</span>
        </a>
    </li>

    <li class="sidebar-item">
        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form-t').submit();"
           class="sidebar-link">
            <i class="bi bi-box-arrow-left"></i>
            <span>Logout</span>
        </a>
        <form id="logout-form-t" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </li>
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
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="stats-icon blue">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Active Classes</h6>
                                    <h6 class="font-extrabold mb-0">4</h6>
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
                                        <i class="bi bi-journal-bookmark-fill"></i>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Courses Published</h6>
                                    <h6 class="font-extrabold mb-0">12</h6>
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
                                        <i class="bi bi-person-badge-fill"></i>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Total Students</h6>
                                    <h6 class="font-extrabold mb-0">87</h6>
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
                                        <i class="bi bi-stars"></i>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">AI Drafts Pending</h6>
                                    <h6 class="font-extrabold mb-0">5</h6>
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
                                    @php
                                        $classes = [
                                            ['name'=>'BSIT 2-A','subject'=>'Data Structures',     'students'=>32,'courses'=>3,'status'=>'active'],
                                            ['name'=>'BSIT 2-B','subject'=>'Web Development',     'students'=>28,'courses'=>4,'status'=>'active'],
                                            ['name'=>'BSCS 3-A','subject'=>'Algorithm Design',    'students'=>30,'courses'=>2,'status'=>'active'],
                                            ['name'=>'BSCS 1-B','subject'=>'Intro to Programming','students'=>35,'courses'=>3,'status'=>'draft'],
                                        ];
                                    @endphp
                                    @foreach($classes as $class)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <p class="font-bold mb-0 ms-3">{{ $class['name'] }}</p>
                                                </div>
                                                <small class="text-muted ms-3">{{ $class['subject'] }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-person-fill text-muted me-1"></i>
                                                    <p class="mb-0">{{ $class['students'] }}</p>
                                                </div>
                                            </td>
                                            <td>{{ $class['courses'] }}</td>
                                            <td>
                                                @if($class['status'] === 'active')
                                                    <span class="badge bg-light-success">Active</span>
                                                @else
                                                    <span class="badge bg-light-warning">Draft</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-outline-primary">Manage</a>
                                            </td>
                                        </tr>
                                    @endforeach
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
                                    @php
                                        $activities = [
                                            ['name'=>'Juan Dela Cruz','course'=>'Data Structures', 'activity'=>'Completed Quiz',       'score'=>'92%','time'=>'2m ago'],
                                            ['name'=>'Maria Santos',  'course'=>'Web Development', 'activity'=>'Generated Flashcards', 'score'=>'—',  'time'=>'15m ago'],
                                            ['name'=>'Carlo Reyes',   'course'=>'Algorithm Design','activity'=>'Completed Quiz',       'score'=>'78%','time'=>'1h ago'],
                                            ['name'=>'Ana Flores',    'course'=>'Data Structures', 'activity'=>'Studied Materials',    'score'=>'—',  'time'=>'2h ago'],
                                            ['name'=>'Mark Lim',      'course'=>'Web Development', 'activity'=>'Completed Quiz',       'score'=>'85%','time'=>'3h ago'],
                                        ];
                                    @endphp
                                    @foreach($activities as $a)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-md">
                                                    <span class="d-flex align-items-center justify-content-center w-100 h-100 rounded-circle bg-light fw-bold" style="color:#435ebe;">
                                                        {{ strtoupper(substr($a['name'],0,1)) }}
                                                    </span>
                                                    </div>
                                                    <p class="font-bold ms-3 mb-0">{{ $a['name'] }}</p>
                                                </div>
                                            </td>
                                            <td class="text-muted">{{ $a['course'] }}</td>
                                            <td>{{ $a['activity'] }}</td>
                                            <td><p class="font-bold mb-0">{{ $a['score'] }}</p></td>
                                            <td class="text-muted">{{ $a['time'] }}</td>
                                        </tr>
                                    @endforeach
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
                        <div class="avatar avatar-xl me-3">
                        <span class="d-flex align-items-center justify-content-center w-100 h-100 rounded-circle bg-danger text-white fw-bold fs-4">
                            {{ strtoupper(substr(auth()->user()->name ?? 'T', 0, 1)) }}
                        </span>
                        </div>
                        <div>
                            <h5 class="font-bold mb-0">{{ auth()->user()->name ?? 'Teacher' }}</h5>
                            <h6 class="text-muted mb-0">Instructor</h6>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('teacher.ai-generate') }}" class="btn btn-primary btn-sm w-100 font-bold">
                            <i class="bi bi-stars me-1"></i> AI Builder
                        </a>
                        <a href="{{ route('teacher.classes') }}" class="btn btn-outline-primary btn-sm w-100 font-bold">
                            <i class="bi bi-plus-lg me-1"></i> New Class
                        </a>
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
                        Upload your materials and let AI generate course content, quizzes, and lessons automatically.
                    </p>
                    <a href="{{ route('teacher.ai-generate') }}"
                       class="btn btn-block btn-xl btn-light font-bold w-100">
                        <i class="bi bi-lightning-charge-fill text-primary me-1"></i> Generate Course
                    </a>
                </div>
            </div>

            {{-- Pending AI Drafts --}}
            <div class="card">
                <div class="card-header">
                    <h4>Pending AI Drafts</h4>
                    <span class="badge bg-warning text-dark">5</span>
                </div>
                <div class="card-content pb-4">
                    @php
                        $drafts = [
                            ['title'=>'Chapter 3: Linked Lists', 'course'=>'Data Structures'],
                            ['title'=>'Intro to REST APIs',       'course'=>'Web Development'],
                            ['title'=>'Big-O Notation Quiz',      'course'=>'Algorithm Design'],
                        ];
                    @endphp
                    @foreach($drafts as $draft)
                        <div class="recent-message d-flex px-4 py-3">
                            <div class="avatar avatar-md">
                        <span class="d-flex align-items-center justify-content-center w-100 h-100 rounded-circle"
                              style="background:#fef3dc;color:#f39c12;">
                            <i class="bi bi-stars"></i>
                        </span>
                            </div>
                            <div class="name ms-4 flex-grow-1">
                                <h5 class="mb-1">{{ $draft['title'] }}</h5>
                                <h6 class="text-muted mb-0">{{ $draft['course'] }}</h6>
                            </div>
                            <div class="d-flex gap-1 align-items-center">
                                <button class="btn btn-sm btn-success px-2 py-1"><i class="bi bi-check-lg"></i></button>
                                <button class="btn btn-sm btn-outline-danger px-2 py-1"><i class="bi bi-x-lg"></i></button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </section>
@endsection
