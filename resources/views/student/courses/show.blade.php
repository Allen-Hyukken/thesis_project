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

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if ($course->description)
        <p class="text-muted">{{ $course->description }}</p>
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
    </ul>

    <div class="tab-content">

        {{-- ============================== LESSONS ============================== --}}
        <div class="tab-pane fade show active" id="pane-lessons">
            @forelse ($course->lessons as $lesson)
                <div class="card mb-2">
                    <div class="card-body">
                        <h6 class="font-bold mb-2">{{ $loop->iteration }}. {{ $lesson->title }}</h6>
                        <p class="mb-0" style="white-space:pre-wrap;font-size:14px;">{{ $lesson->content }}</p>
                    </div>
                </div>
            @empty
                <p class="text-muted">No lessons posted yet.</p>
            @endforelse
        </div>

        {{-- ============================== ACTIVITIES ============================== --}}
        <div class="tab-pane fade" id="pane-activities">
            @forelse ($course->activities as $activity)
                @php $sub = $activitySubmissions[$activity->module_id] ?? null; @endphp
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="font-bold mb-0">{{ $activity->title }}</h6>
                            <span class="badge bg-light text-dark border">{{ ucfirst($activity->activity_type) }} • {{ $activity->points }} pts</span>
                        </div>
                        <p class="mb-3" style="white-space:pre-wrap;font-size:14px;">{{ $activity->content }}</p>
                        @if ($activity->due_at)
                            <p class="text-muted mb-3" style="font-size:12px;"><i class="bi bi-clock"></i> Due {{ $activity->due_at->format('M j, Y g:i A') }}</p>
                        @endif

                        @if ($sub && $sub->isGraded())
                            <div class="alert alert-success mb-0">
                                <strong>Score: {{ $sub->score }}/{{ $activity->points }}</strong>
                                @if ($sub->feedback)
                                    <p class="mb-0 mt-1" style="font-size:13px;">{{ $sub->feedback }}</p>
                                @endif
                            </div>
                        @else
                            <form action="{{ route('student.activities.submit', $activity->module_id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label font-bold" style="font-size:13px;">Your answer</label>
                                    <textarea name="submission_text" class="form-control" rows="4">{{ old('submission_text', $sub->submission_text ?? '') }}</textarea>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label font-bold" style="font-size:13px;">Attach a file (optional)</label>
                                    <input type="file" name="file" class="form-control">
                                    @if ($sub && $sub->file_original_name)
                                        <p class="text-muted mt-1 mb-0" style="font-size:12px;">Currently attached: {{ $sub->file_original_name }}</p>
                                    @endif
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm font-bold">
                                    {{ $sub ? 'Resubmit' : 'Submit' }}
                                </button>
                                @if ($sub && ! $sub->isGraded())
                                    <span class="text-muted ms-2" style="font-size:12px;">Submitted — awaiting grade.</span>
                                @endif
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted">No activities posted yet.</p>
            @endforelse
        </div>

        {{-- ============================== QUIZZES ============================== --}}
        <div class="tab-pane fade" id="pane-quizzes">
            @forelse ($course->quizzes as $quiz)
                @php $sub = $quizSubmissions[$quiz->quiz_id] ?? null; @endphp
                <div class="card mb-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="font-bold mb-1">{{ $quiz->title }}</h6>
                            <span class="text-muted" style="font-size:12px;">{{ $quiz->questions->count() }} questions</span>
                        </div>
                        @if (! $quiz->is_published)
                            <span class="badge bg-light text-dark border">Not yet available</span>
                        @elseif ($sub)
                            <a href="{{ route('student.quizzes.take', $quiz->quiz_id) }}" class="btn btn-sm btn-outline-primary font-bold">
                                View Result ({{ $sub->score }}/{{ $sub->max_score }})
                            </a>
                        @else
                            <a href="{{ route('student.quizzes.take', $quiz->quiz_id) }}" class="btn btn-sm btn-primary font-bold">
                                Take Quiz
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted">No quizzes posted yet.</p>
            @endforelse
        </div>

        {{-- ============================== EXAMS ============================== --}}
        <div class="tab-pane fade" id="pane-exams">
            @forelse ($course->exams as $exam)
                @php $sub = $examSubmissions[$exam->exam_id] ?? null; @endphp
                <div class="card mb-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="font-bold mb-1">{{ $exam->title }}</h6>
                            <span class="text-muted" style="font-size:12px;">{{ $exam->questions->count() }} questions</span>
                        </div>
                        @if (! $exam->is_published)
                            <span class="badge bg-light text-dark border">Not yet available</span>
                        @elseif ($sub)
                            <a href="{{ route('student.exams.take', $exam->exam_id) }}" class="btn btn-sm btn-outline-primary font-bold">
                                View Result ({{ $sub->score }}/{{ $sub->max_score }})
                            </a>
                        @else
                            <a href="{{ route('student.exams.take', $exam->exam_id) }}" class="btn btn-sm btn-primary font-bold">
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
