@extends('layouts.app')

@section('title', $class->class_name)

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
@endsection

@section('page-heading')
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h3 class="mb-1">{{ $class->class_name }}</h3>
            <span class="text-muted" style="font-size:13px;">
                {{ $class->subject }} • {{ $class->teacher->full_name ?? 'Unknown teacher' }}
            </span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-light font-bold" data-bs-toggle="offcanvas" data-bs-target="#filesDrawer">
                <i class="bi bi-folder-fill me-1"></i> Files
            </button>
            <button class="btn btn-light font-bold" data-bs-toggle="offcanvas" data-bs-target="#membersDrawer">
                <i class="bi bi-people-fill me-1"></i> Members
            </button>
            <button class="btn btn-light font-bold" data-bs-toggle="offcanvas" data-bs-target="#myScoresDrawer">
                <i class="bi bi-bar-chart-fill me-1"></i> My Scores
            </button>
        </div>
    </div>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <h5 class="font-bold mb-3">Courses</h5>

    <div class="row">
        @forelse ($class->postedCourses as $course)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="font-bold mb-1">{{ $course->title }}</h6>
                        @if ($course->description)
                            <p class="text-muted mb-3" style="font-size:13px;">{{ \Illuminate\Support\Str::limit($course->description, 90) }}</p>
                        @endif
                        <div class="d-flex gap-3 text-muted mb-3" style="font-size:12px;">
                            <span><i class="bi bi-journal-text"></i> {{ $course->lessons->count() }}</span>
                            <span><i class="bi bi-clipboard-check"></i> {{ $course->activities->count() }}</span>
                            <span><i class="bi bi-patch-question"></i> {{ $course->quizzes->count() }}</span>
                            <span><i class="bi bi-file-text"></i> {{ $course->exams->count() }}</span>
                        </div>
                        <a href="{{ route('student.classes.courses.show', [$class->class_id, $course->course_id]) }}" class="btn btn-primary btn-sm w-100 font-bold">
                            Open Course
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-journal-plus fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="font-bold">No courses posted yet</h5>
                        <p class="text-muted mb-0">Your teacher hasn't posted any courses to this class yet.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @include('student.classes.partials.files-drawer')
    @include('student.classes.partials.members-drawer')
    @include('student.classes.partials.my-scores-drawer')

@endsection
