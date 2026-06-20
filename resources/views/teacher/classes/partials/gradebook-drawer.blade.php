<div class="offcanvas offcanvas-end" tabindex="-1" id="gradebookDrawer" style="width:480px;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title font-bold">Gradebook</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">

        @forelse ($class->postedCourses as $course)
            <details open class="mb-3">
                <summary class="font-bold" style="cursor:pointer;font-size:14px;">{{ $course->title }}</summary>
                <div class="ps-2 mt-2">

                    {{-- Activities --}}
                    @foreach ($course->activities as $activity)
                        <details class="mb-2">
                            <summary style="cursor:pointer;font-size:13px;">
                                <i class="bi bi-clipboard-check"></i> {{ $activity->title }}
                            </summary>
                            <div class="ps-2 mt-1">
                                @php $subs = $gradebook['activitySubmissions'][$activity->module_id] ?? collect(); @endphp
                                @forelse ($subs as $sub)
                                    <div class="border rounded p-2 mb-2">
                                        <p class="mb-1" style="font-size:12px;">
                                            <strong>{{ $sub->student->full_name }}</strong>
                                            <span class="badge {{ $sub->isGraded() ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $sub->isGraded() ? $sub->score . '/' . $activity->points : 'Needs grading' }}
                                            </span>
                                        </p>
                                        @if ($sub->submission_text)
                                            <p class="text-muted mb-1" style="font-size:11px;">{{ \Illuminate\Support\Str::limit($sub->submission_text, 120) }}</p>
                                        @endif
                                        @if ($sub->file_path)
                                            <p class="mb-1" style="font-size:11px;"><i class="bi bi-paperclip"></i> {{ $sub->file_original_name }}</p>
                                        @endif
                                        <form action="{{ route('teacher.gradebook.activities.grade', $sub->submission_id) }}" method="POST">
                                            @csrf
                                            <div class="d-flex gap-1 align-items-center mb-1">
                                                <input type="number" name="score" min="0" max="{{ $activity->points }}" step="0.01"
                                                       value="{{ $sub->score }}" class="form-control form-control-sm" style="width:70px;" required>
                                                <span style="font-size:11px;">/ {{ $activity->points }}</span>
                                            </div>
                                            <textarea name="feedback" class="form-control form-control-sm mb-1" rows="2" placeholder="Feedback (optional)">{{ $sub->feedback }}</textarea>
                                            <button type="submit" class="btn btn-sm btn-primary w-100">Save Grade</button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="text-muted" style="font-size:11px;">No submissions yet.</p>
                                @endforelse
                            </div>
                        </details>
                    @endforeach

                    {{-- Quizzes --}}
                    @foreach ($course->quizzes as $quiz)
                        <details class="mb-2">
                            <summary style="cursor:pointer;font-size:13px;">
                                <i class="bi bi-patch-question"></i> {{ $quiz->title }}
                            </summary>
                            <div class="ps-2 mt-1">
                                @php $subs = $gradebook['quizSubmissions'][$quiz->quiz_id] ?? collect(); @endphp
                                @forelse ($subs as $sub)
                                    <div class="border rounded p-2 mb-2">
                                        <p class="mb-1" style="font-size:12px;">
                                            <strong>{{ $sub->student->full_name }}</strong>
                                            <span class="badge {{ $sub->needsReview() ? 'bg-warning text-dark' : 'bg-success' }}">
                                                {{ $sub->score }}/{{ $sub->max_score }} {{ $sub->needsReview() ? '(pending review)' : '' }}
                                            </span>
                                        </p>
                                        @foreach ($sub->answers as $answer)
                                            @if ($answer->is_correct === null && $answer->points_earned === null)
                                                <div class="mb-2">
                                                    <p class="mb-1" style="font-size:11px;">{{ $answer->question->question_text }}</p>
                                                    <p class="text-muted mb-1" style="font-size:11px;">"{{ $answer->answer_text }}"</p>
                                                    <form action="{{ route('teacher.gradebook.quiz-answers.grade', $answer->answer_id) }}" method="POST" class="d-flex gap-1">
                                                        @csrf
                                                        <input type="number" name="points_earned" min="0" max="{{ $answer->question->points }}" step="0.01"
                                                               class="form-control form-control-sm" style="width:70px;" placeholder="Pts" required>
                                                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                                    </form>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @empty
                                    <p class="text-muted" style="font-size:11px;">No submissions yet.</p>
                                @endforelse
                            </div>
                        </details>
                    @endforeach

                    {{-- Exams --}}
                    @foreach ($course->exams as $exam)
                        <details class="mb-2">
                            <summary style="cursor:pointer;font-size:13px;">
                                <i class="bi bi-file-text"></i> {{ $exam->title }}
                            </summary>
                            <div class="ps-2 mt-1">
                                @php $subs = $gradebook['examSubmissions'][$exam->exam_id] ?? collect(); @endphp
                                @forelse ($subs as $sub)
                                    <div class="border rounded p-2 mb-2">
                                        <p class="mb-1" style="font-size:12px;">
                                            <strong>{{ $sub->student->full_name }}</strong>
                                            <span class="badge {{ $sub->needsReview() ? 'bg-warning text-dark' : 'bg-success' }}">
                                                {{ $sub->score }}/{{ $sub->max_score }} {{ $sub->needsReview() ? '(pending review)' : '' }}
                                            </span>
                                        </p>
                                        @foreach ($sub->answers as $answer)
                                            @if ($answer->is_correct === null && $answer->points_earned === null)
                                                <div class="mb-2">
                                                    <p class="mb-1" style="font-size:11px;">{{ $answer->question->question_text }}</p>
                                                    <p class="text-muted mb-1" style="font-size:11px;">"{{ $answer->answer_text }}"</p>
                                                    <form action="{{ route('teacher.gradebook.exam-answers.grade', $answer->answer_id) }}" method="POST" class="d-flex gap-1">
                                                        @csrf
                                                        <input type="number" name="points_earned" min="0" max="{{ $answer->question->points }}" step="0.01"
                                                               class="form-control form-control-sm" style="width:70px;" placeholder="Pts" required>
                                                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                                    </form>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @empty
                                    <p class="text-muted" style="font-size:11px;">No submissions yet.</p>
                                @endforelse
                            </div>
                        </details>
                    @endforeach

                    @if ($course->activities->isEmpty() && $course->quizzes->isEmpty() && $course->exams->isEmpty())
                        <p class="text-muted" style="font-size:12px;">This course has no activities, quizzes, or exams yet.</p>
                    @endif
                </div>
            </details>
        @empty
            <p class="text-muted">Post a course to this class to start seeing scores here.</p>
        @endforelse

    </div>
</div>
