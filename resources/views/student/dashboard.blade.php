@extends('layouts.app')

@section('title', 'Student Dashboard')

{{-- ===== STUDENT SIDEBAR NAV ===== --}}
@section('sidebar-nav')
    <div class="nav-section-label">Main</div>
    <div class="nav-item">
        <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span class="nav-label">Dashboard</span>
        </a>
    </div>

    <div class="nav-section-label">Learning</div>
    <div class="nav-item">
        <a href="#" class="nav-link">
            <i class="bi bi-book-half"></i>
            <span class="nav-label">My Enrolled Classes</span>
        </a>
    </div>
    <div class="nav-item">
        <a href="#" class="nav-link">
            <i class="bi bi-journal-text"></i>
            <span class="nav-label">Browse Courses</span>
        </a>
    </div>

    <div class="nav-section-label">AI Study Tools</div>
    <div class="nav-item">
        <a href="#" class="nav-link">
            <i class="bi bi-layers-fill"></i>
            <span class="nav-label">AI Flashcards</span>
            <span class="badge bg-info">New</span>
        </a>
    </div>
    <div class="nav-item">
        <a href="#" class="nav-link">
            <i class="bi bi-chat-quote-fill"></i>
            <span class="nav-label">AI Tutor Chat</span>
            <span class="ai-badge">AI</span>
        </a>
    </div>

    <div class="nav-section-label">Account</div>
    <div class="nav-item">
        <a href="#" class="nav-link">
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

{{-- ===== STUDENT PAGE CONTENT ===== --}}
@section('content')
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h4>Welcome back, {{ explode(' ', auth()->user()->name ?? 'Student')[0] }}! 👋</h4>
            <p>Ready to continue your AI-powered learning journey?</p>
        </div>
        <div>
            <button class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle"></i> Join a Class
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-mortarboard-fill"></i></div>
                <div class="stat-info"><strong>3</strong><span>Enrolled Classes</span></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-info"><strong>85%</strong><span>Avg. Score</span></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon gold"><i class="bi bi-lightning-fill"></i></div>
                <div class="stat-info"><strong>124</strong><span>Flashcards Mastered</span></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-fire"></i></div>
                <div class="stat-info"><strong>5 Days</strong><span>Study Streak</span></div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- My Classes --}}
        <div class="col-12 col-lg-8">
            <div class="content-card">
                <div class="card-header">
                    <h6><i class="bi bi-book me-2 text-primary"></i>Continue Learning</h6>
                </div>
                <div class="card-body">
                    @php
                        $myClasses = [
                            ['name'=>'Data Structures','teacher'=>'Dr. Ramos','progress'=>75, 'color'=>'#435ebe'],
                            ['name'=>'Web Development','teacher'=>'Prof. Sy','progress'=>40, 'color'=>'#198754'],
                            ['name'=>'Intellectual Property','teacher'=>'Atty. Cruz','progress'=>10, 'color'=>'#c0392b'],
                        ];
                    @endphp
                    @foreach($myClasses as $class)
                        <div class="course-item py-3">
                            <div class="course-thumb" style="background: #f0f4ff; color: {{ $class['color'] }}">
                                <i class="bi bi-journal-code"></i>
                            </div>
                            <div class="course-info flex-grow-1">
                                <strong>{{ $class['name'] }}</strong>
                                <span>Instructor: {{ $class['teacher'] }}</span>
                                <div class="progress mt-2" style="height: 5px;">
                                    <div class="progress-bar" style="width: {{ $class['progress'] }}%; background-color: {{ $class['color'] }}"></div>
                                </div>
                            </div>
                            <div class="ps-3 text-end">
                                <span class="d-block fw-bold text-dark">{{ $class['progress'] }}%</span>
                                <a href="#" class="btn btn-sm btn-link p-0 text-decoration-none">Open</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- AI Study Assistant Sidebar --}}
        <div class="col-12 col-lg-4">
            <div class="flashcard-preview mb-3">
                <div>
                    <span class="ai-badge mb-2"><i class="bi bi-stars"></i> AI SUGGESTION</span>
                    <h5 class="mb-1 fw-bold">Review Needed!</h5>
                    <p class="small opacity-75">You haven't reviewed "Big-O Notation" flashcards in 3 days.</p>
                </div>
                <button class="btn btn-light btn-sm fw-bold w-100">Start Review</button>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h6><i class="bi bi-chat-dots me-2 text-info"></i>Ask AI Tutor</h6>
                </div>
                <div class="card-body">
                    <div class="ai-bubble mb-3">
                        Hi! I'm your TUP AI Tutor. Ask me anything about your current courses!
                    </div>
                    <div class="position-relative">
                        <input type="text" class="form-control form-control-sm pe-5" placeholder="What is a Linked List?">
                        <button class="btn btn-sm text-primary position-absolute end-0 top-50 translate-middle-y">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
