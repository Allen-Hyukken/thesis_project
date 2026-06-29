{{--
    Teacher gradebook — activity submissions panel.
    Required: $activity (CourseModule), $subs (Collection<ActivitySubmission>)
--}}
<div class="tac-gradebook-panel mb-4">

    {{-- Panel header --}}
    <div class="tac-gb-header d-flex align-items-center gap-2 mb-2">
        <i class="bi bi-clipboard-check text-primary"></i>
        <span class="fw-bold" style="color:#25396f; font-size:14px;">{{ $activity->title }}</span>
        <span class="badge bg-light text-secondary border ms-auto">{{ $subs->count() }} submission{{ $subs->count() !== 1 ? 's' : '' }}</span>
        <span class="badge bg-warning text-dark">
            {{ $subs->where('status','submitted')->count() }} ungraded
        </span>
    </div>

    @forelse ($subs as $sub)
        @php
            $isGraded = $sub->isGraded();
            $mime     = $sub->file_mime_type ?? '';
            $ext      = strtolower(pathinfo($sub->file_original_name ?? '', PATHINFO_EXTENSION));
            $canPreview = str_starts_with($mime, 'image/')
                || str_starts_with($mime, 'video/')
                || str_starts_with($mime, 'audio/')
                || $mime === 'application/pdf';
            $previewUrl  = route('student.activities.submissions.preview',  $sub->submission_id);
            $downloadUrl = route('student.activities.submissions.download', $sub->submission_id);
            $fileIcon = match(true) {
                str_starts_with($mime, 'image/')   => 'bi-file-earmark-image text-success',
                str_starts_with($mime, 'video/')   => 'bi-file-earmark-play text-danger',
                str_starts_with($mime, 'audio/')   => 'bi-file-earmark-music text-warning',
                $mime === 'application/pdf'        => 'bi-file-earmark-pdf text-danger',
                in_array($ext, ['doc','docx'])     => 'bi-file-earmark-word text-primary',
                in_array($ext, ['xls','xlsx'])     => 'bi-file-earmark-excel text-success',
                in_array($ext, ['ppt','pptx'])     => 'bi-file-earmark-ppt text-warning',
                default                            => 'bi-file-earmark text-secondary',
            };
        @endphp

        <div class="tac-gb-card {{ $isGraded ? 'tac-gb-graded' : 'tac-gb-pending' }}">

            {{-- Student info row --}}
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
                            Submitted {{ \Carbon\Carbon::parse($sub->submitted_at)->format('M j, Y g:i A') }}
                        </div>
                    </div>
                </div>
                <div>
                    @if ($isGraded)
                        <span class="badge bg-success fs-6">{{ $sub->score }}/{{ $activity->points }}</span>
                    @else
                        <span class="badge bg-warning text-dark">Needs grading</span>
                    @endif
                </div>
            </div>

            {{-- Submission content --}}
            @if ($sub->submission_text)
                <div class="tac-gb-response">
                    <div class="tac-gb-section-label">Response</div>
                    <div class="tac-text-bubble">{{ $sub->submission_text }}</div>
                </div>
            @endif

            {{-- Attached file --}}
            @if ($sub->file_path)
                <div class="tac-gb-response mt-2">
                    <div class="tac-gb-section-label">Attachment</div>
                    <div class="tac-file-chip">
                        <i class="bi {{ $fileIcon }} fs-5"></i>
                        <span class="tac-file-chip-name">{{ $sub->file_original_name }}</span>
                        <div class="tac-file-chip-actions">
                            @if ($canPreview)
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="previewSubmissionFile('{{ $previewUrl }}', '{{ $sub->file_original_name }}', '{{ $mime }}', '{{ $downloadUrl }}')">
                                    <i class="bi bi-eye me-1"></i> View
                                </button>
                            @endif
                            <a href="{{ $downloadUrl }}" class="btn btn-sm btn-outline-secondary" title="Download">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Feedback (if graded) --}}
            @if ($isGraded && $sub->feedback)
                <div class="tac-gb-response mt-2">
                    <div class="tac-gb-section-label">Your feedback</div>
                    <div class="tac-feedback-text">{{ $sub->feedback }}</div>
                </div>
            @endif

            {{-- Grade form --}}
            <div class="tac-gb-grade-form mt-3">
                <form action="{{ route('teacher.gradebook.activities.grade', $sub->submission_id) }}" method="POST">
                    @csrf
                    <div class="d-flex gap-2 align-items-end flex-wrap">
                        <div>
                            <label class="tac-gb-section-label mb-1">Score</label>
                            <div class="d-flex align-items-center gap-1">
                                <input type="number" name="score"
                                       min="0" max="{{ $activity->points }}" step="0.01"
                                       value="{{ $sub->score ?? '' }}"
                                       class="form-control form-control-sm"
                                       style="width:75px;" required>
                                <span class="text-muted" style="font-size:13px;">/ {{ $activity->points }}</span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <label class="tac-gb-section-label mb-1">Feedback <span class="text-muted">(optional)</span></label>
                            <input type="text" name="feedback"
                                   value="{{ $sub->feedback ?? '' }}"
                                   class="form-control form-control-sm"
                                   placeholder="Write feedback for the student…">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-check-lg me-1"></i> {{ $isGraded ? 'Update' : 'Save Grade' }}
                        </button>
                    </div>
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

<style>
    /* Gradebook panel */
    .tac-gradebook-panel { }
    .tac-gb-header { padding: 8px 0; border-bottom: 2px solid #ebf3ff; margin-bottom: 10px; }
    .tac-gb-card {
        border-radius: 10px;
        border: 1px solid #dfe3e7;
        padding: 14px 16px;
        margin-bottom: 10px;
        background: #fff;
    }
    .tac-gb-graded  { border-left: 4px solid #4fbe87; }
    .tac-gb-pending { border-left: 4px solid #f6c23e; }
    .tac-gb-student-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .tac-gb-response { margin-top: 8px; }
    .tac-gb-section-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #6c757d;
        margin-bottom: 4px;
    }
    .tac-gb-grade-form {
        border-top: 1px solid #f0f2f5;
        padding-top: 12px;
    }
</style>
