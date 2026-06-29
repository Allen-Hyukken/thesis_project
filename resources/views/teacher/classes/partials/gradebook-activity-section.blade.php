{{--
    Teacher gradebook — activity submissions panel.
    Required: $activity (course module model), $subs (Collection)
--}}

<div class="gb2-sub-list">
    @forelse ($subs as $sub)
        @php $graded = $sub->isGraded(); @endphp

        <div class="tac-gb-card {{ $graded ? 'tac-gb-graded' : 'tac-gb-pending' }}">

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
                            Submitted {{ $sub->submitted_at ? $sub->submitted_at->diffForHumans() : '—' }}
                        </div>
                    </div>
                </div>
                <div>
                    @if ($graded)
                        <span class="badge bg-success fs-6">{{ $sub->score }}/{{ $activity->points }}</span>
                    @else
                        <span class="badge bg-warning text-dark">Needs grading</span>
                    @endif
                </div>
            </div>

            <div class="tac-gb-response">
                @if ($sub->submission_text)
                    <div class="tac-gb-section-label">Submission</div>
                    <div class="tac-text-bubble mb-2">{{ $sub->submission_text }}</div>
                @endif

                @if ($sub->file_path)
                    <div class="mb-2">
                        <a href="{{ Storage::url($sub->file_path) }}" target="_blank"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-paperclip"></i> {{ $sub->file_original_name }}
                        </a>
                    </div>
                @endif

                <form action="{{ route('teacher.gradebook.activities.grade', $sub->submission_id) }}" method="POST">
                    @csrf
                    <div class="d-flex gap-2 align-items-center mb-2">
                        <input type="number" name="score"
                               min="0" max="{{ $activity->points }}" step="0.01"
                               value="{{ $sub->score }}"
                               class="form-control form-control-sm" style="width:80px;" required>
                        <span class="text-muted" style="font-size:13px;">/ {{ $activity->points }} pts</span>
                    </div>
                    <textarea name="feedback" class="form-control form-control-sm mb-2"
                              rows="2" placeholder="Feedback (optional)">{{ $sub->feedback }}</textarea>
                    <button type="submit" class="btn btn-sm btn-primary w-100">Save Grade</button>
                </form>
            </div>

        </div>
    @empty
        <div class="text-muted text-center py-3" style="font-size:13px;">
            <i class="bi bi-inbox fs-4 d-block mb-1 opacity-50"></i>
            No submissions yet.
        </div>
    @endforelse
</div>
