@extends('layouts.app')

@section('title', $class->class_name . ' — My Scores')

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
@endsection

@section('page-heading')
    @include('student.classes.partials.class-header')
@endsection

@section('content')

    @include('student.classes.partials.class-nav')

    @if ($class->postedCourses->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-bar-chart fs-1 text-muted mb-3 d-block"></i>
                <h5 class="font-bold">No scores yet</h5>
                <p class="text-muted mb-0">Your teacher hasn't posted any courses to this class yet.</p>
            </div>
        </div>
    @else
        <div class="accordion" id="scoresAccordion">
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
                                <p class="text-muted mb-0">Nothing to take in this course yet.</p>
                            @endif

                            @foreach ($course->activities as $activity)
                                @php $sub = $myActivitySubmissions[$activity->module_id] ?? null; @endphp
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="bi bi-clipboard-check me-2 text-muted"></i>{{ $activity->title }}</span>
                                    @if (! $sub)
                                        <span class="badge bg-light text-dark border">Not submitted</span>
                                    @elseif ($sub->isGraded())
                                        <span class="badge bg-success">{{ $sub->score }}/{{ $activity->points }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Awaiting grade</span>
                                    @endif
                                </div>
                            @endforeach

                            @foreach ($course->quizzes as $quiz)
                                @php $sub = $myQuizSubmissions[$quiz->quiz_id] ?? null; @endphp
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="bi bi-patch-question me-2 text-muted"></i>{{ $quiz->title }}</span>
                                    @if (! $sub)
                                        <a href="{{ route('student.quizzes.take', $quiz->quiz_id) }}" class="badge bg-primary text-decoration-none">Take Quiz</a>
                                    @else
                                        <span class="badge {{ $sub->needsReview() ? 'bg-warning text-dark' : 'bg-success' }}">
                                            {{ $sub->score }}/{{ $sub->max_score }} {{ $sub->needsReview() ? '(pending)' : '' }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach

                            @foreach ($course->exams as $exam)
                                @php $sub = $myExamSubmissions[$exam->exam_id] ?? null; @endphp
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="bi bi-file-text me-2 text-muted"></i>{{ $exam->title }}</span>
                                    @if (! $sub)
                                        <a href="{{ route('student.exams.take', $exam->exam_id) }}" class="badge bg-primary text-decoration-none">Take Exam</a>
                                    @else
                                        <span class="badge {{ $sub->needsReview() ? 'bg-warning text-dark' : 'bg-success' }}">
                                            {{ $sub->score }}/{{ $sub->max_score }} {{ $sub->needsReview() ? '(pending)' : '' }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endsection
