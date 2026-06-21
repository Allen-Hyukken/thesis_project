@extends('layouts.app')

@section('title', 'My Courses')

@section('sidebar-nav')
    @include('teacher.partials.sidebar-nav')
@endsection

@section('page-heading')
    <div class="d-flex justify-content-between align-items-center">
        <h3>My Courses</h3>
        <a href="{{ route('teacher.courses.create') }}" class="btn btn-primary font-bold">
            <i class="bi bi-plus-circle me-1"></i> Create Course
        </a>
    </div>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        @forelse ($courses as $course)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="font-bold mb-0">{{ $course->title }}</h5>
                            @if ($course->ai_generated)
                                <span class="badge bg-light text-primary border" title="Built with AI assistance">
                                    <i class="bi bi-stars"></i>
                                </span>
                            @endif
                        </div>

                        <div class="mb-2">
                            <span class="badge {{ $course->status === 'published' ? 'bg-success' : 'bg-light text-dark border' }}">
                                {{ ucfirst($course->status) }}
                            </span>
                        </div>

                        @if ($course->description)
                            <p class="text-muted mb-3" style="font-size:13px;">
                                {{ \Illuminate\Support\Str::limit($course->description, 100) }}
                            </p>
                        @endif

                        <div class="d-flex gap-3 text-muted mb-3" style="font-size:12px;">
                            <span><i class="bi bi-journal-text"></i> {{ $course->lessons_count }} lessons</span>
                            <span><i class="bi bi-clipboard-check"></i> {{ $course->activities_count }} activities</span>
                            <span><i class="bi bi-patch-question"></i> {{ $course->quizzes_count }} quizzes</span>
                            <span><i class="bi bi-file-text"></i> {{ $course->exams_count }} exams</span>
                        </div>

                        <a href="{{ route('teacher.courses.show', $course->course_id) }}" class="btn btn-outline-primary btn-sm w-100 font-bold">
                            Manage Course
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-journal-plus fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="font-bold">No courses yet</h5>
                        <p class="text-muted mb-3">Create your first course — AI can help draft the outline.</p>
                        <a href="{{ route('teacher.courses.create') }}" class="btn btn-primary font-bold">
                            <i class="bi bi-plus-circle me-1"></i> Create Course
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

@endsection
