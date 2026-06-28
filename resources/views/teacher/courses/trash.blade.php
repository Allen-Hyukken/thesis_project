@extends('layouts.app')

@section('title', 'Course Trash')

@section('sidebar-nav')
    @include('teacher.partials.sidebar-nav')
@endsection

@section('page-heading')
    <div class="d-flex justify-content-between align-items-center">
        <h3 class="mb-1"><i class="bi bi-trash3 me-1"></i> Course Trash</h3>
        <a href="{{ route('teacher.courses') }}" class="btn btn-outline-secondary font-bold">
            <i class="bi bi-arrow-left me-1"></i> Back to Courses
        </a>
    </div>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <p class="text-muted" style="font-size:13px;">
        Courses in trash are hidden from students. You can restore them or permanently delete them.
    </p>

    @forelse ($courses as $course)
        <div class="card mb-2">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="font-bold mb-1">{{ $course->title }}</h6>
                    <small class="text-muted">
                        Deleted {{ $course->deleted_at->diffForHumans() }}
                        @if ($course->classRoom)
                            · {{ $course->classRoom->class_name }}
                        @endif
                    </small>
                </div>
                <div class="d-flex gap-2">
                    {{-- Restore --}}
                    <form action="{{ route('teacher.courses.restore', $course->course_id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success font-bold">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                        </button>
                    </form>
                    {{-- Permanently delete --}}
                    <form action="{{ route('teacher.courses.force-delete', $course->course_id) }}"
                          method="POST"
                          onsubmit="return confirm('Permanently delete {{ e($course->title) }}? This cannot be undone — all lessons, quizzes, and exams will be lost forever.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger font-bold">
                            <i class="bi bi-trash me-1"></i> Delete Forever
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-muted text-center py-5">
                <i class="bi bi-trash3 fs-1 d-block mb-2 opacity-25"></i>
                Trash is empty.
            </div>
        </div>
    @endforelse

@endsection
