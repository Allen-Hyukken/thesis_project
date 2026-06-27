{{--
    MS Teams-style activity card partial.
    Required: $activity (CourseModule), $sub (ActivitySubmission|null)
    Context: student course show page
--}}
@php
    $isGraded    = $sub && $sub->isGraded();
    $isSubmitted = $sub && ! $isGraded;

    $typeColors = [
        'assignment'  => ['bg' => '#ebf3ff', 'text' => '#435ebe', 'icon' => 'bi-journal-text'],
        'discussion'  => ['bg' => '#fff4e5', 'text' => '#d4860b', 'icon' => 'bi-chat-dots'],
        'project'     => ['bg' => '#e8f8f0', 'text' => '#1a8a4a', 'icon' => 'bi-kanban'],
        'reflection'  => ['bg' => '#f3ebff', 'text' => '#7c4daa', 'icon' => 'bi-lightbulb'],
    ];
    $tc = $typeColors[$activity->activity_type] ?? $typeColors['assignment'];
@endphp

<div class="teams-activity-card mb-3" id="activity-{{ $activity->module_id }}">

    {{-- ── Header bar ──────────────────────────────────────────────── --}}
    <div class="tac-header d-flex align-items-center gap-3">
        <div class="tac-type-icon" style="background:{{ $tc['bg'] }}; color:{{ $tc['text'] }};">
            <i class="bi {{ $tc['icon'] }} fs-5"></i>
        </div>
        <div class="flex-grow-1">
            <div class="tac-title">{{ $activity->title }}</div>
            <div class="tac-meta">
                <span class="tac-badge" style="background:{{ $tc['bg'] }}; color:{{ $tc['text'] }};">
                    {{ ucfirst($activity->activity_type) }}
                </span>
                <span class="tac-dot">·</span>
                <span>{{ $activity->points }} pts</span>
                @if ($activity->due_at)
                    <span class="tac-dot">·</span>
                    <i class="bi bi-clock me-1"></i>
                    Due {{ \Carbon\Carbon::parse($activity->due_at)->format('M j, Y g:i A') }}
                @endif
            </div>
        </div>
        {{-- Status chip --}}
        @if ($isGraded)
            <div class="tac-status-chip tac-chip-graded">
                <i class="bi bi-check-circle-fill me-1"></i>
                {{ $sub->score }}/{{ $activity->points }}
            </div>
        @elseif ($isSubmitted)
            <div class="tac-status-chip tac-chip-submitted">
                <i class="bi bi-hourglass-split me-1"></i> Submitted
            </div>
        @else
            <div class="tac-status-chip tac-chip-pending">
                <i class="bi bi-circle me-1"></i> Not submitted
            </div>
        @endif
    </div>

    {{-- ── Instructions ────────────────────────────────────────────── --}}
    <div class="tac-body">
        <div class="tac-instructions">{{ $activity->content }}</div>

        {{-- ── Graded feedback banner ───────────────────────────────── --}}
        @if ($isGraded)
            <div class="tac-feedback-box">
                <div class="tac-feedback-score">
                    <i class="bi bi-trophy-fill me-2"></i>
                    Grade: <strong>{{ $sub->score }} / {{ $activity->points }}</strong>
                </div>
                @if ($sub->feedback)
                    <div class="tac-feedback-text">
                        <div class="tac-feedback-label">Feedback from teacher</div>
                        {{ $sub->feedback }}
                    </div>
                @endif
                {{-- Show submitted content even after grading (read-only) --}}
                @if ($sub->submission_text)
                    <div class="tac-submitted-text mt-3">
                        <div class="tac-feedback-label">Your response</div>
                        <div class="tac-text-bubble">{{ $sub->submission_text }}</div>
                    </div>
                @endif
                @if ($sub->file_path)
                    <div class="tac-submitted-file mt-2">
                        <div class="tac-feedback-label">Your attachment</div>
                        @include('student.classes.partials.submission-file-chip', ['sub' => $sub])
                    </div>
                @endif
            </div>

            {{-- ── Submitted but not yet graded ────────────────────────── --}}
        @elseif ($isSubmitted)
            <div class="tac-submitted-banner">
                <i class="bi bi-send-check-fill me-2 text-primary"></i>
                <span>Submitted {{ \Carbon\Carbon::parse($sub->submitted_at)->format('M j, Y g:i A') }} — awaiting grade.</span>
            </div>

            {{-- Show what was submitted --}}
            @if ($sub->submission_text)
                <div class="tac-submitted-preview mt-3">
                    <div class="tac-preview-label">Your response</div>
                    <div class="tac-text-bubble">{{ $sub->submission_text }}</div>
                </div>
            @endif
            @if ($sub->file_path)
                <div class="tac-submitted-preview mt-2">
                    <div class="tac-preview-label">Your attachment</div>
                    @include('student.classes.partials.submission-file-chip', ['sub' => $sub])
                </div>
            @endif

            {{-- Re-submit form --}}
            <div class="tac-form-section mt-3">
                <button class="btn btn-sm btn-outline-secondary" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#resubmit-{{ $activity->module_id }}">
                    <i class="bi bi-pencil me-1"></i> Edit & Resubmit
                </button>
                <div class="collapse mt-2" id="resubmit-{{ $activity->module_id }}">
                    @include('student.classes.partials.activity-submit-form', compact('activity', 'sub'))
                </div>
            </div>

            {{-- ── Not yet submitted ────────────────────────────────────── --}}
        @else
            <div class="tac-form-section">
                @include('student.classes.partials.activity-submit-form', compact('activity', 'sub'))
            </div>
        @endif
    </div>
</div>
