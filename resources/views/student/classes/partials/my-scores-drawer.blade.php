<div class="offcanvas offcanvas-end" tabindex="-1" id="myScoresDrawer" style="width:420px;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title font-bold">My Scores</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">

        @forelse ($class->postedCourses as $course)
            <details open class="mb-3">
                <summary class="font-bold" style="cursor:pointer;font-size:14px;">{{ $course->title }}</summary>
                <div class="ps-2 mt-2">

                    @foreach ($course->activities as $activity)
                        @php $sub = $myActivitySubmissions[$activity->module_id] ?? null; @endphp
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="font-size:13px;"><i class="bi bi-clipboard-check me-1"></i>{{ $activity->title }}</span>
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
                            <span style="font-size:13px;"><i class="bi bi-patch-question me-1"></i>{{ $quiz->title }}</span>
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
                            <span style="font-size:13px;"><i class="bi bi-file-text me-1"></i>{{ $exam->title }}</span>
                            @if (! $sub)
                                <a href="{{ route('student.exams.take', $exam->exam_id) }}" class="badge bg-primary text-decoration-none">Take Exam</a>
                            @else
                                <span class="badge {{ $sub->needsReview() ? 'bg-warning text-dark' : 'bg-success' }}">
                                    {{ $sub->score }}/{{ $sub->max_score }} {{ $sub->needsReview() ? '(pending)' : '' }}
                                </span>
                            @endif
                        </div>
                    @endforeach

                    @if ($course->activities->isEmpty() && $course->quizzes->isEmpty() && $course->exams->isEmpty())
                        <p class="text-muted" style="font-size:12px;">Nothing to take in this course yet.</p>
                    @endif
                </div>
            </details>
        @empty
            <p class="text-muted">No courses posted yet.</p>
        @endforelse

    </div>
</div>
