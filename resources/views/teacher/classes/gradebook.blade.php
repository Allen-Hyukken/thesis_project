@extends('layouts.app')

@section('title', $class->class_name . ' — Gradebook')

@section('sidebar-nav')
    @include('teacher.partials.sidebar-nav')
@endsection

@section('page-heading')
    @include('teacher.classes.partials.class-header')
@endsection

@section('content')

    @include('teacher.classes.partials.class-nav')

    {{-- Submission preview modal + styles (shared) --}}
    @include('student.classes.partials.activity-styles')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($class->postedCourses->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-bar-chart fs-1 text-muted mb-3 d-block"></i>
                <h5 class="fw-bold">Nothing to grade yet</h5>
                <p class="text-muted mb-0">Post a course to this class to start seeing scores here.</p>
            </div>
        </div>
    @else
        <div class="accordion" id="gradebookAccordion">
            @foreach ($class->postedCourses as $i => $course)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }} fw-bold" type="button"
                                data-bs-toggle="collapse" data-bs-target="#course-{{ $course->course_id }}">
                            {{ $course->title }}
                        </button>
                    </h2>
                    <div id="course-{{ $course->course_id }}"
                         class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}">
                        <div class="accordion-body">

                            @if ($course->activities->isEmpty() && $course->quizzes->isEmpty() && $course->exams->isEmpty())
                                <p class="text-muted mb-0">This course has no activities, quizzes, or exams yet.</p>
                            @endif

                            {{-- ── Activities (Teams-style cards) ── --}}
                            @foreach ($course->activities as $activity)
                                @php $subs = $gradebook['activitySubmissions'][$activity->module_id] ?? collect(); @endphp
                                @include('teacher.classes.partials.gradebook-activity-section', compact('activity', 'subs'))
                            @endforeach

                            {{-- ── Quizzes ── --}}
                            @foreach ($course->quizzes as $quiz)
                                <h6 class="fw-bold mt-3 mb-2">
                                    <i class="bi bi-patch-question me-1 text-primary"></i> {{ $quiz->title }}
                                </h6>
                                @php $subs = $gradebook['quizSubmissions'][$quiz->quiz_id] ?? collect(); @endphp
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm table-hover align-middle">
                                        <thead>
                                        <tr><th>Student</th><th>Score</th><th class="text-end">Action</th></tr>
                                        </thead>
                                        <tbody>
                                        @forelse ($subs as $sub)
                                            <tr>
                                                <td>{{ $sub->student->full_name }}</td>
                                                <td>
                                                    <span class="badge {{ $sub->needsReview() ? 'bg-warning text-dark' : 'bg-success' }}">
                                                        {{ $sub->score }}/{{ $sub->max_score }}
                                                        {{ $sub->needsReview() ? '(pending)' : '' }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    @if ($sub->needsReview())
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#review-quiz-{{ $sub->submission_id }}">
                                                            Review
                                                        </button>
                                                    @else
                                                        <span class="text-muted" style="font-size:12px;">Fully graded</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-muted">No submissions yet.</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach

                            {{-- ── Exams ── --}}
                            @foreach ($course->exams as $exam)
                                <h6 class="fw-bold mt-3 mb-2">
                                    <i class="bi bi-file-text me-1 text-primary"></i> {{ $exam->title }}
                                </h6>
                                @php $subs = $gradebook['examSubmissions'][$exam->exam_id] ?? collect(); @endphp
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm table-hover align-middle">
                                        <thead>
                                        <tr><th>Student</th><th>Score</th><th class="text-end">Action</th></tr>
                                        </thead>
                                        <tbody>
                                        @forelse ($subs as $sub)
                                            <tr>
                                                <td>{{ $sub->student->full_name }}</td>
                                                <td>
                                                    <span class="badge {{ $sub->needsReview() ? 'bg-warning text-dark' : 'bg-success' }}">
                                                        {{ $sub->score }}/{{ $sub->max_score }}
                                                        {{ $sub->needsReview() ? '(pending)' : '' }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    @if ($sub->needsReview())
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#review-exam-{{ $sub->submission_id }}">
                                                            Review
                                                        </button>
                                                    @else
                                                        <span class="text-muted" style="font-size:12px;">Fully graded</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-muted">No submissions yet.</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── Modals (outside accordion to avoid display:none inheritance) ── --}}
    @foreach ($class->postedCourses as $course)

        @foreach ($course->quizzes as $quiz)
            @foreach (($gradebook['quizSubmissions'][$quiz->quiz_id] ?? collect()) as $sub)
                @continue(! $sub->needsReview())
                <div class="modal fade" id="review-quiz-{{ $sub->submission_id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">
                                    Review: {{ $sub->student->full_name }} — {{ $quiz->title }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                @foreach ($sub->answers as $answer)
                                    @if ($answer->is_correct === null && $answer->points_earned === null)
                                        <div class="border rounded p-3 mb-3">
                                            <p class="fw-semibold mb-1" style="font-size:13px;">{{ $answer->question->question_text }}</p>
                                            <div class="tac-text-bubble mb-2">{{ $answer->answer_text }}</div>
                                            <form action="{{ route('teacher.gradebook.quiz-answers.grade', $answer->answer_id) }}"
                                                  method="POST" class="d-flex gap-2 align-items-center">
                                                @csrf
                                                <input type="number" name="points_earned"
                                                       min="0" max="{{ $answer->question->points }}" step="0.01"
                                                       class="form-control form-control-sm" style="width:80px;"
                                                       placeholder="Pts" required>
                                                <span class="text-muted" style="font-size:13px;">/ {{ $answer->question->points }} pts</span>
                                                <button type="submit" class="btn btn-sm btn-primary ms-auto">Save</button>
                                            </form>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach

        @foreach ($course->exams as $exam)
            @foreach (($gradebook['examSubmissions'][$exam->exam_id] ?? collect()) as $sub)
                @continue(! $sub->needsReview())
                <div class="modal fade" id="review-exam-{{ $sub->submission_id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">
                                    Review: {{ $sub->student->full_name }} — {{ $exam->title }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                @foreach ($sub->answers as $answer)
                                    @if ($answer->is_correct === null && $answer->points_earned === null)
                                        <div class="border rounded p-3 mb-3">
                                            <p class="fw-semibold mb-1" style="font-size:13px;">{{ $answer->question->question_text }}</p>
                                            <div class="tac-text-bubble mb-2">{{ $answer->answer_text }}</div>
                                            <form action="{{ route('teacher.gradebook.exam-answers.grade', $answer->answer_id) }}"
                                                  method="POST" class="d-flex gap-2 align-items-center">
                                                @csrf
                                                <input type="number" name="points_earned"
                                                       min="0" max="{{ $answer->question->points }}" step="0.01"
                                                       class="form-control form-control-sm" style="width:80px;"
                                                       placeholder="Pts" required>
                                                <span class="text-muted" style="font-size:13px;">/ {{ $answer->question->points }} pts</span>
                                                <button type="submit" class="btn btn-sm btn-primary ms-auto">Save</button>
                                            </form>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach

    @endforeach

@endsection

@push('scripts')
    @include('student.classes.partials.activity-scripts')
@endpush
