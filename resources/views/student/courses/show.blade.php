@extends('layouts.app')

@section('title', $course->title)

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
@endsection

@section('page-heading')
    <div>
        <h3 class="mb-1">{{ $course->title }}</h3>
        <a href="{{ route('student.classes.show', $class->class_id) }}" class="text-muted" style="font-size:13px;">
            <i class="bi bi-arrow-left"></i> Back to {{ $class->class_name }}
        </a>
    </div>
@endsection

@section('content')

    @include('student.classes.partials.activity-styles')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if ($course->description)
        <p class="text-muted mb-3">{{ $course->description }}</p>
    @endif

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pane-lessons">
                <i class="bi bi-journal-text me-1"></i> Lessons
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-activities">
                <i class="bi bi-clipboard-check me-1"></i> Activities
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-quizzes">
                <i class="bi bi-patch-question me-1"></i> Quizzes
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-exams">
                <i class="bi bi-file-text me-1"></i> Exams
            </button>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('student.classes.courses.ai-tutor', [$class->class_id, $course->course_id]) }}">
                <i class="bi bi-robot me-1"></i> EDITH
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('student.classes.courses.flashcards', [$class->class_id, $course->course_id]) }}">
                <i class="bi bi-collection me-1"></i> Flashcards
            </a>
        </li>
    </ul>

    <div class="tab-content">

        {{-- LESSONS --}}
        <div class="tab-pane fade show active" id="pane-lessons">
            @forelse ($course->lessons as $lesson)
                <div class="card mb-2">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">{{ $loop->iteration }}. {{ $lesson->title }}</h6>
                        <p class="mb-0" style="white-space:pre-wrap;font-size:14px;">{{ $lesson->content }}</p>
                    </div>
                </div>
            @empty
                <p class="text-muted">No lessons posted yet.</p>
            @endforelse
        </div>

        {{-- ACTIVITIES --}}
        <div class="tab-pane fade" id="pane-activities">
            @forelse ($course->activities as $activity)
                @php $sub = $activitySubmissions[$activity->module_id] ?? null; @endphp
                @include('student.classes.partials.activity-card', compact('activity', 'sub'))
            @empty
                <p class="text-muted">No activities posted yet.</p>
            @endforelse
        </div>

        {{-- QUIZZES --}}
        <div class="tab-pane fade" id="pane-quizzes">
            @forelse ($course->quizzes as $quiz)
                @php $sub = $quizSubmissions[$quiz->quiz_id] ?? null; @endphp
                <div class="card mb-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-1">{{ $quiz->title }}</h6>
                            <span class="text-muted" style="font-size:12px;">{{ $quiz->questions->count() }} questions</span>
                        </div>
                        @if (! $quiz->is_published)
                            <span class="badge bg-light text-dark border">Not yet available</span>
                        @elseif ($sub)
                            <a href="{{ route('student.quizzes.take', $quiz->quiz_id) }}" class="btn btn-sm btn-outline-primary fw-bold">
                                View Result ({{ $sub->score }}/{{ $sub->max_score }})
                            </a>
                        @else
                            <a href="{{ route('student.quizzes.take', $quiz->quiz_id) }}" class="btn btn-sm btn-primary fw-bold">
                                Take Quiz
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted">No quizzes posted yet.</p>
            @endforelse
        </div>

        {{-- EXAMS --}}
        <div class="tab-pane fade" id="pane-exams">
            @forelse ($course->exams as $exam)
                @php $sub = $examSubmissions[$exam->exam_id] ?? null; @endphp
                <div class="card mb-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-1">{{ $exam->title }}</h6>
                            <span class="text-muted" style="font-size:12px;">{{ $exam->questions->count() }} questions</span>
                        </div>
                        @if (! $exam->is_published)
                            <span class="badge bg-light text-dark border">Not yet available</span>
                        @elseif ($sub)
                            <a href="{{ route('student.exams.take', $exam->exam_id) }}" class="btn btn-sm btn-outline-primary fw-bold">
                                View Result ({{ $sub->score }}/{{ $sub->max_score }})
                            </a>
                        @else
                            <a href="{{ route('student.exams.take', $exam->exam_id) }}" class="btn btn-sm btn-primary fw-bold">
                                Take Exam
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted">No exams posted yet.</p>
            @endforelse
        </div>

    </div>

@endsection

@push('scripts')
    @include('student.classes.partials.activity-scripts')
@endpush
