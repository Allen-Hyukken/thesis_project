@extends('layouts.app')

@section('title', $class->class_name)

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
@endsection

@section('page-heading')
    @include('student.classes.partials.class-header')
@endsection

@section('content')

    @include('student.classes.partials.class-nav')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <h5 class="font-bold mb-3">Courses</h5>

    <div class="row">
        @forelse ($class->postedCourses as $course)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card mb-3 h-100">
                    <div class="card-body d-flex flex-column">
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
                        <a href="{{ route('student.classes.courses.show', [$class->class_id, $course->course_id]) }}" class="btn btn-primary btn-sm w-100 font-bold mt-auto">
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

@endsection
