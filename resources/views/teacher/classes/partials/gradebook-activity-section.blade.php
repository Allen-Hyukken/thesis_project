{{--
    Teacher gradebook — quiz/exam submissions panel (shared).
    Required: $assessment (Quiz|Exam), $subs (Collection), $type ('quiz'|'exam')
--}}
@php
    $gradeRoute = 'teacher.gradebook.' . $type . '-answers.grade';
@endphp

<div class="gb2-sub-list">
    @forelse ($subs as $sub)
        @php $needsReview = $sub->needsReview(); @endphp

        <div class="tac-gb-card {{ $needsReview ? 'tac-gb-pending' : 'tac-gb-graded' }}">

            <div class="tac-gb-student-row">
                <div class="d-flex align-items-center gap-2">
                    @if ($sub->student->avatar)
                        <img src="{{ $sub->student->avatar }}"
                             class="rounded-circle" style="width:34px;height:34px;object-fit:cover;">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                             style="width:34px;height:34px;background:#435ebe;font-size:13px;">
                            {{ strtoupper(substr($sub->student->full_name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div class="fw-semibold" style="font-size:14px;color:#25396f;">{{ $sub->student->full_name }}</div>
                        <div class="text-muted" style="font-size:11px;">
                            {{ $sub->answers->count() }} question{{ $sub->answers->count() === 1 ? '' : 's' }}
                        </div>
                    </div>
                </div>
                <div>
                    @if ($needsReview)
                        <span class="badge bg-warning text-dark">Needs review</span>
                    @else
                        <span class="badge bg-success fs-6">{{ $sub->score }}/{{ $sub->max_score }}</span>
                    @endif
                </div>
            </div>

            @if ($needsReview)
                <div class="tac-gb-response">
                    <div class="tac-gb-section-label">Open-ended answers awaiting review</div>
                    @foreach ($sub->answers as $answer)
                        @continue(! ($answer->is_correct === null && $answer->points_earned === null))
                        <div class="gb2-answer-block">
                            <p class="gb2-answer-question">{{ $answer->question->question_text }}</p>
                            <div class="tac-text-bubble mb-2">{{ $answer->answer_text }}</div>
                            <form action="{{ route($gradeRoute, $answer->answer_id) }}" method="POST" class="d-flex gap-2 align-items-center">
                                @csrf
                                <input type="number" name="points_earned"
                                       min="0" max="{{ $answer->question->points }}" step="0.01"
                                       class="form-control form-control-sm" style="width:80px;"
                                       placeholder="Pts" required>
                                <span class="text-muted" style="font-size:13px;">/ {{ $answer->question->points }} pts</span>
                                <button type="submit" class="btn btn-sm btn-primary ms-auto">Save</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="tac-gb-response">
                    <div class="tac-gb-section-label">Score breakdown</div>
                    <div class="gb2-answer-summary">
                        @foreach ($sub->answers as $answer)
                            <div class="gb2-answer-row">
                                <span>{{ \Illuminate\Support\Str::limit($answer->question->question_text, 70) }}</span>
                                <span class="{{ $answer->is_correct ? 'text-success' : ($answer->points_earned > 0 ? 'text-warning' : 'text-danger') }}">
                                    {{ $answer->points_earned ?? 0 }}/{{ $answer->question->points }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    @empty
        <div class="text-muted text-center py-3" style="font-size:13px;">
            <i class="bi bi-inbox fs-4 d-block mb-1 opacity-50"></i>
            No submissions yet.
        </div>
    @endforelse
</div>
