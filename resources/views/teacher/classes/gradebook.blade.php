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

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($class->postedCourses->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-bar-chart fs-1 text-muted mb-3 d-block"></i>
                <h5 class="font-bold">Nothing to grade yet</h5>
                <p class="text-muted mb-0">Post a course to this class to start seeing scores here.</p>
            </div>
        </div>
    @else
        <div class="accordion" id="gradebookAccordion">
            @foreach ($class->postedCourses as $i => $course)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }} font-bold" type="button"
                                data-bs-toggle="collapse" data-bs-target="#course-{{ $course->course_id }}">
                            {{ $course->title }}
                        </button>
                    </h2>
                    <div id="course-{{ $course->course_id }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}">
                        <div class="accordion-body">

                            @if ($course->activities->isEmpty() && $course->quizzes->isEmpty() && $course->exams->isEmpty())
                                <p class="text-muted mb-0">This course has no activities, quizzes, or exams yet.</p>
                            @endif

                            {{-- Activities --}}
                            @foreach ($course->activities as $activity)
                                <h6 class="font-bold mt-2"><i class="bi bi-clipboard-check me-1"></i> {{ $activity->title }}</h6>
                                @php $subs = $gradebook['activitySubmissions'][$activity->module_id] ?? collect(); @endphp
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
                                                    @if ($sub->isGraded())
                                                        <span class="badge bg-success">{{ $sub->score }}/{{ $activity->points }}</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">Needs grading</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                            data-bs-toggle="modal" data-bs-target="#grade-activity-{{ $sub->submission_id }}">
                                                        {{ $sub->isGraded() ? 'Edit Grade' : 'Grade' }}
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-muted">No submissions yet.</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach

                            {{-- Quizzes --}}
                            @foreach ($course->quizzes as $quiz)
                                <h6 class="font-bold mt-2"><i class="bi bi-patch-question me-1"></i> {{ $quiz->title }}</h6>
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
                                                        {{ $sub->score }}/{{ $sub->max_score }} {{ $sub->needsReview() ? '(pending)' : '' }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    @if ($sub->needsReview())
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                                data-bs-toggle="modal" data-bs-target="#review-quiz-{{ $sub->submission_id }}">
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

                            {{-- Exams --}}
                            @foreach ($course->exams as $exam)
                                <h6 class="font-bold mt-2"><i class="bi bi-file-text me-1"></i> {{ $exam->title }}</h6>
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
                                                        {{ $sub->score }}/{{ $sub->max_score }} {{ $sub->needsReview() ? '(pending)' : '' }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    @if ($sub->needsReview())
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                                data-bs-toggle="modal" data-bs-target="#review-exam-{{ $sub->submission_id }}">
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

    {{-- ============================================================
         Grading modals — rendered OUTSIDE the accordion on purpose.
         A modal nested inside a collapsed .accordion-collapse would
         inherit display:none from its ancestor and never be able to
         show, even when Bootstrap toggles the modal's own classes.
         ============================================================ --}}

    @foreach ($class->postedCourses as $course)

        @foreach ($course->activities as $activity)
            @foreach (($gradebook['activitySubmissions'][$activity->module_id] ?? collect()) as $sub)
                <div class="modal fade" id="grade-activity-{{ $sub->submission_id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('teacher.gradebook.activities.grade', $sub->submission_id) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title font-bold">Grade: {{ $sub->student->full_name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="font-bold mb-1" style="font-size:13px;">{{ $activity->title }}</p>
                                    @if ($sub->submission_text)
                                        <p class="text-muted mb-2" style="font-size:13px;">{{ $sub->submission_text }}</p>
                                    @endif
                                    @if ($sub->file_path)
                                        <p class="mb-2" style="font-size:13px;"><i class="bi bi-paperclip"></i> {{ $sub->file_original_name }}</p>
                                    @endif
                                    <div class="d-flex gap-2 align-items-center mb-2">
                                        <input type="number" name="score" min="0" max="{{ $activity->points }}" step="0.01"
                                               value="{{ $sub->score }}" class="form-control" style="width:100px;" required>
                                        <span>/ {{ $activity->points }} pts</span>
                                    </div>
                                    <textarea name="feedback" class="form-control" rows="3" placeholder="Feedback (optional)">{{ $sub->feedback }}</textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary font-bold">Save Grade</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach

        @foreach ($course->quizzes as $quiz)
            @foreach (($gradebook['quizSubmissions'][$quiz->quiz_id] ?? collect()) as $sub)
                @continue(! $sub->needsReview())
                <div class="modal fade" id="review-quiz-{{ $sub->submission_id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title font-bold">Review: {{ $sub->student->full_name }} — {{ $quiz->title }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                @foreach ($sub->answers as $answer)
                                    @if ($answer->is_correct === null && $answer->points_earned === null)
                                        <div class="border rounded p-2 mb-2">
                                            <p class="mb-1" style="font-size:13px;">{{ $answer->question->question_text }}</p>
                                            <p class="text-muted mb-2" style="font-size:13px;">"{{ $answer->answer_text }}"</p>
                                            <form action="{{ route('teacher.gradebook.quiz-answers.grade', $answer->answer_id) }}" method="POST" class="d-flex gap-2">
                                                @csrf
                                                <input type="number" name="points_earned" min="0" max="{{ $answer->question->points }}" step="0.01"
                                                       class="form-control" style="width:100px;" placeholder="Pts" required>
                                                <span class="align-self-center text-muted">/ {{ $answer->question->points }} pts</span>
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
                                <h5 class="modal-title font-bold">Review: {{ $sub->student->full_name }} — {{ $exam->title }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                @foreach ($sub->answers as $answer)
                                    @if ($answer->is_correct === null && $answer->points_earned === null)
                                        <div class="border rounded p-2 mb-2">
                                            <p class="mb-1" style="font-size:13px;">{{ $answer->question->question_text }}</p>
                                            <p class="text-muted mb-2" style="font-size:13px;">"{{ $answer->answer_text }}"</p>
                                            <form action="{{ route('teacher.gradebook.exam-answers.grade', $answer->answer_id) }}" method="POST" class="d-flex gap-2">
                                                @csrf
                                                <input type="number" name="points_earned" min="0" max="{{ $answer->question->points }}" step="0.01"
                                                       class="form-control" style="width:100px;" placeholder="Pts" required>
                                                <span class="align-self-center text-muted">/ {{ $answer->question->points }} pts</span>
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
