@extends('layouts.app')

@section('title', 'Teacher Dashboard')

{{-- ===== SIDEBAR NAV ===== --}}
@section('sidebar-nav')
    <div class="nav-section-label">Main</div>

    <div class="nav-item">
        <a href="{{ route('teacher.dashboard') }}" class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span class="nav-label">Dashboard</span>
        </a>
    </div>

    <div class="nav-section-label">Teaching</div>

    <div class="nav-item">
        <a href="{{ route('teacher.classes') }}" class="nav-link {{ request()->routeIs('teacher.classes') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i>
            <span class="nav-label">My Classes</span>
            <span class="badge bg-primary rounded-pill">4</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="{{ route('teacher.courses') }}" class="nav-link {{ request()->routeIs('teacher.courses') ? 'active' : '' }}">
            <i class="bi bi-journal-bookmark-fill"></i>
            <span class="nav-label">Courses</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="{{ route('teacher.materials') }}" class="nav-link {{ request()->routeIs('teacher.materials') ? 'active' : '' }}">
            <i class="bi bi-folder-fill"></i>
            <span class="nav-label">Learning Materials</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="{{ route('teacher.students') }}" class="nav-link {{ request()->routeIs('teacher.students') ? 'active' : '' }}">
            <i class="bi bi-person-badge-fill"></i>
            <span class="nav-label">Students</span>
        </a>
    </div>

    <div class="nav-section-label">AI Tools</div>

    <div class="nav-item">
        <a href="{{ route('teacher.ai-generate') }}" class="nav-link {{ request()->routeIs('teacher.ai-generate') ? 'active' : '' }}">
            <i class="bi bi-stars"></i>
            <span class="nav-label">AI Course Builder</span>
            <span class="badge" style="background:linear-gradient(135deg,#435ebe,#6f42c1);color:#fff;border-radius:20px;">AI</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="{{ route('teacher.results') }}" class="nav-link {{ request()->routeIs('teacher.results') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-fill"></i>
            <span class="nav-label">Results & Analytics</span>
        </a>
    </div>

    <div class="nav-section-label">Account</div>

    <div class="nav-item">
        <a href="{{ route('teacher.profile') }}" class="nav-link {{ request()->routeIs('teacher.profile') ? 'active' : '' }}">
            <i class="bi bi-person-circle"></i>
            <span class="nav-label">My Profile</span>
        </a>
    </div>

    <div class="nav-item">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-link w-100 border-0 bg-transparent text-start">
                <i class="bi bi-box-arrow-left"></i>
                <span class="nav-label">Logout</span>
            </button>
        </form>
    </div>
@endsection

{{-- ===== PAGE CONTENT ===== --}}
@section('content')

    {{-- Page Header --}}
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h4>Good morning, {{ explode(' ', auth()->user()->name ?? 'Teacher')[0] }} 👋</h4>
            <p>Here's what's happening in your classes today.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('teacher.ai-generate') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-stars"></i> AI Course Builder
            </a>
            <a href="{{ route('teacher.classes') }}" class="btn btn-outline-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> New Class
            </a>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-people-fill"></i></div>
                <div class="stat-info">
                    <strong>4</strong>
                    <span>Active Classes</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-journal-bookmark-fill"></i></div>
                <div class="stat-info">
                    <strong>12</strong>
                    <span>Courses Published</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon gold"><i class="bi bi-person-badge-fill"></i></div>
                <div class="stat-info">
                    <strong>87</strong>
                    <span>Total Students</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-stars"></i></div>
                <div class="stat-info">
                    <strong>5</strong>
                    <span>AI Drafts Pending</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- Recent Classes --}}
        <div class="col-12 col-lg-7">
            <div class="content-card h-100">
                <div class="card-header">
                    <h6><i class="bi bi-people-fill me-2 text-primary"></i>My Classes</h6>
                    <a href="{{ route('teacher.classes') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:14px;">
                            <thead>
                            <tr style="background:#f8faff;">
                                <th class="ps-4 py-3 text-muted fw-bold" style="font-size:12px;">CLASS</th>
                                <th class="py-3 text-muted fw-bold" style="font-size:12px;">STUDENTS</th>
                                <th class="py-3 text-muted fw-bold" style="font-size:12px;">COURSES</th>
                                <th class="py-3 text-muted fw-bold" style="font-size:12px;">STATUS</th>
                                <th class="pe-4 py-3"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $classes = [
                                    ['name'=>'BSIT 2-A','subject'=>'Data Structures','students'=>32,'courses'=>3,'status'=>'active'],
                                    ['name'=>'BSIT 2-B','subject'=>'Web Development','students'=>28,'courses'=>4,'status'=>'active'],
                                    ['name'=>'BSCS 3-A','subject'=>'Algorithm Design','students'=>30,'courses'=>2,'status'=>'active'],
                                    ['name'=>'BSCS 1-B','subject'=>'Intro to Programming','students'=>35,'courses'=>3,'status'=>'draft'],
                                ];
                            @endphp
                            @foreach($classes as $class)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <strong style="color:#25396f;">{{ $class['name'] }}</strong>
                                        <div style="font-size:12px;color:#7c8db5;">{{ $class['subject'] }}</div>
                                    </td>
                                    <td class="py-3">
                                    <span class="d-flex align-items-center gap-1">
                                        <i class="bi bi-person-fill" style="color:#7c8db5;font-size:12px;"></i>
                                        {{ $class['students'] }}
                                    </span>
                                    </td>
                                    <td class="py-3">{{ $class['courses'] }}</td>
                                    <td class="py-3">
                                        @if($class['status'] === 'active')
                                            <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size:11px;border-radius:20px;padding:4px 10px;">Active</span>
                                        @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning fw-bold" style="font-size:11px;border-radius:20px;padding:4px 10px;">Draft</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 py-3">
                                        <a href="#" class="btn btn-sm btn-outline-primary" style="font-size:12px;">Manage</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-12 col-lg-5 d-flex flex-column gap-3">

            {{-- AI Course Builder CTA --}}
            <div style="background:linear-gradient(135deg,#435ebe 0%,#6f42c1 100%);border-radius:14px;padding:24px;color:#fff;">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-stars" style="font-size:20px;"></i>
                    <span style="font-weight:800;font-size:15px;">AI Course Builder</span>
                </div>
                <p style="font-size:13px;opacity:.85;margin-bottom:16px;">
                    Upload your learning materials and let AI automatically generate course content, quizzes, and structured lessons for your students.
                </p>
                <a href="{{ route('teacher.ai-generate') }}" class="btn btn-light btn-sm fw-bold d-inline-flex align-items-center gap-2">
                    <i class="bi bi-lightning-charge-fill text-primary"></i> Generate Course
                </a>
            </div>

            {{-- Pending AI Drafts --}}
            <div class="content-card">
                <div class="card-header">
                    <h6><i class="bi bi-clock-history me-2" style="color:#f39c12;"></i>Pending AI Drafts</h6>
                    <span class="badge bg-warning text-dark">5</span>
                </div>
                <div class="card-body">
                    @php
                        $drafts = [
                            ['title'=>'Chapter 3: Linked Lists','course'=>'Data Structures'],
                            ['title'=>'Intro to REST APIs','course'=>'Web Development'],
                            ['title'=>'Big-O Notation Quiz','course'=>'Algorithm Design'],
                        ];
                    @endphp
                    @foreach($drafts as $draft)
                        <div class="course-item">
                            <div class="course-thumb" style="background:#fef3dc;color:#f39c12;">
                                <i class="bi bi-stars"></i>
                            </div>
                            <div class="course-info flex-grow-1">
                                <strong>{{ $draft['title'] }}</strong>
                                <span>{{ $draft['course'] }}</span>
                            </div>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-success py-1 px-2" style="font-size:12px;"><i class="bi bi-check-lg"></i></button>
                                <button class="btn btn-sm btn-outline-danger py-1 px-2" style="font-size:12px;"><i class="bi bi-x-lg"></i></button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Recent Student Activity --}}
        <div class="col-12">
            <div class="content-card">
                <div class="card-header">
                    <h6><i class="bi bi-activity me-2 text-success"></i>Recent Student Activity</h6>
                    <a href="{{ route('teacher.results') }}" class="btn btn-sm btn-outline-primary">Analytics</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:14px;">
                            <thead>
                            <tr style="background:#f8faff;">
                                <th class="ps-4 py-3 text-muted fw-bold" style="font-size:12px;">STUDENT</th>
                                <th class="py-3 text-muted fw-bold" style="font-size:12px;">COURSE</th>
                                <th class="py-3 text-muted fw-bold" style="font-size:12px;">ACTIVITY</th>
                                <th class="py-3 text-muted fw-bold" style="font-size:12px;">SCORE</th>
                                <th class="pe-4 py-3 text-muted fw-bold" style="font-size:12px;">TIME</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $activities = [
                                    ['name'=>'Juan Dela Cruz','course'=>'Data Structures','activity'=>'Completed Quiz','score'=>'92%','time'=>'2m ago','color'=>'#198754'],
                                    ['name'=>'Maria Santos','course'=>'Web Development','activity'=>'Generated Flashcards','score'=>'—','time'=>'15m ago','color'=>'#6f42c1'],
                                    ['name'=>'Carlo Reyes','course'=>'Algorithm Design','activity'=>'Completed Quiz','score'=>'78%','time'=>'1h ago','color'=>'#f39c12'],
                                    ['name'=>'Ana Flores','course'=>'Data Structures','activity'=>'Studied Materials','score'=>'—','time'=>'2h ago','color'=>'#435ebe'],
                                    ['name'=>'Mark Lim','course'=>'Web Development','activity'=>'Completed Quiz','score'=>'85%','time'=>'3h ago','color'=>'#198754'],
                                ];
                            @endphp
                            @foreach($activities as $a)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:32px;height:32px;border-radius:50%;background:#e8edfa;color:#435ebe;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;flex-shrink:0;">
                                                {{ strtoupper(substr($a['name'],0,1)) }}
                                            </div>
                                            <span style="font-weight:700;color:#25396f;">{{ $a['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3" style="color:#7c8db5;">{{ $a['course'] }}</td>
                                    <td class="py-3">
                                        <span style="font-size:13px;color:{{ $a['color'] }};font-weight:700;">{{ $a['activity'] }}</span>
                                    </td>
                                    <td class="py-3">
                                        <span style="font-weight:800;color:{{ $a['color'] }};">{{ $a['score'] }}</span>
                                    </td>
                                    <td class="pe-4 py-3" style="color:#adb9cc;font-size:13px;">{{ $a['time'] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /.row --}}
@endsection
