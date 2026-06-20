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
                                    <h6 class="font-extrabold mb-0">3</h6>
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
                                    <h6 class="font-extrabold mb-0">85%</h6>
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
                                        <i class="bi bi-lightning-fill"></i>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Flashcards Mastered</h6>
                                    <h6 class="font-extrabold mb-0">124</h6>
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
                                        <i class="bi bi-fire"></i>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="text-muted font-semibold">Study Streak</h6>
                                    <h6 class="font-extrabold mb-0">5 Days</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Continue Learning --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Continue Learning</h4>
                            <a href="{{ route('student.courses') }}" class="btn btn-primary btn-sm">Browse Courses</a>
                        </div>
                        <div class="card-body">
                            @php
                                $myClasses = [
                                    ['name'=>'Data Structures',       'teacher'=>'Dr. Ramos',  'progress'=>75, 'color'=>'#435ebe'],
                                    ['name'=>'Web Development',       'teacher'=>'Prof. Sy',   'progress'=>40, 'color'=>'#198754'],
                                    ['name'=>'Intellectual Property', 'teacher'=>'Atty. Cruz', 'progress'=>10, 'color'=>'#c0392b'],
                                ];
                            @endphp
                            @foreach($myClasses as $class)
                                <div class="d-flex align-items-center mb-4">
                                    <div class="avatar avatar-lg me-3">
                                        <div class="d-flex align-items-center justify-content-center w-100 h-100 rounded bg-light">
                                            <i class="bi bi-journal-code fs-4" style="color:{{ $class['color'] }};"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between mb-1">
                                            <h6 class="mb-0 font-bold">{{ $class['name'] }}</h6>
                                            <span class="font-bold">{{ $class['progress'] }}%</span>
                                        </div>
                                        <p class="text-muted mb-1" style="font-size:12px;">Instructor: {{ $class['teacher'] }}</p>
                                        <div class="progress" style="height:5px;">
                                            <div class="progress-bar" role="progressbar"
                                                 style="width:{{ $class['progress'] }}%;background-color:{{ $class['color'] }};">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <a href="#" class="btn btn-sm btn-outline-primary">Open</a>
                                    </div>
                                </div>
                            @endforeach
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
                        <div class="avatar avatar-xl me-3">
                        <span class="d-flex align-items-center justify-content-center w-100 h-100 rounded-circle bg-danger text-white fw-bold fs-4">
                            {{ strtoupper(substr(auth()->user()->full_name ?? 'S', 0, 1)) }}
                        </span>
                        </div>
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

            {{-- AI Suggestion --}}
            <div class="card" style="background:linear-gradient(135deg,#435ebe 0%,#6f42c1 100%);color:#fff;">
                <div class="card-body">
                <span class="badge bg-white text-primary fw-bold mb-2" style="font-size:11px;">
                    <i class="bi bi-stars me-1"></i> AI SUGGESTION
                </span>
                    <h5 class="font-bold mb-1">Review Needed!</h5>
                    <p style="font-size:13px;opacity:.85;margin-bottom:16px;">
                        You haven't reviewed "Big-O Notation" flashcards in 3 days.
                    </p>
                    <button class="btn btn-block btn-xl btn-light font-bold w-100">
                        Start Review
                    </button>
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
