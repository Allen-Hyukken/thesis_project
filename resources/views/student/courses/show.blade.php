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

    @include('student.classes.partials.activity-styles')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if ($course->description)
        <p class="text-muted mb-3">{{ $course->description }}</p>
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
        <li class="nav-item">
            <a class="nav-link" href="{{ route('student.classes.courses.ai-tutor', [$class->class_id, $course->course_id]) }}">
                <i class="bi bi-robot me-1"></i> EDITH
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('student.classes.courses.flashcards', [$class->class_id, $course->course_id]) }}">
                <i class="bi bi-collection me-1"></i> Flashcards
            </a>
        </li>
    </ul>

    <div class="tab-content">

        {{-- LESSONS --}}
        <div class="tab-pane fade show active" id="pane-lessons">
            @forelse ($course->lessons as $lesson)
                <div class="card mb-3 edith-lesson-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="edith-lesson-num">{{ $loop->iteration }}</span>
                            <h6 class="fw-bold mb-0">{{ $lesson->title }}</h6>
                            @if ($lesson->ai_generated)
                                <span class="badge bg-light text-primary border ms-1" style="font-size:10px;"><i class="bi bi-stars"></i> AI</span>
                            @endif
                        </div>
                        <div class="edith-content" data-raw="{{ e($lesson->content) }}"></div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No lessons posted yet.</p>
            @endforelse
        </div>

        {{-- ACTIVITIES --}}
        <div class="tab-pane fade" id="pane-activities">
            @forelse ($course->activities as $activity)
                @php $sub = $activitySubmissions[$activity->module_id] ?? null; @endphp
                @include('student.classes.partials.activity-card', compact('activity', 'sub'))
            @empty
                <p class="text-muted">No activities posted yet.</p>
            @endforelse
        </div>

        {{-- QUIZZES --}}
        <div class="tab-pane fade" id="pane-quizzes">
            @forelse ($course->quizzes as $quiz)
                @php $sub = $quizSubmissions[$quiz->quiz_id] ?? null; @endphp
                <div class="card mb-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-1">{{ $quiz->title }}</h6>
                            <span class="text-muted" style="font-size:12px;">{{ $quiz->questions->count() }} questions</span>
                        </div>
                        @if (! $quiz->is_published)
                            <span class="badge bg-light text-dark border">Not yet available</span>
                        @elseif ($sub)
                            <a href="{{ route('student.quizzes.take', $quiz->quiz_id) }}" class="btn btn-sm btn-outline-primary fw-bold">
                                View Result ({{ $sub->score }}/{{ $sub->max_score }})
                            </a>
                        @else
                            <a href="{{ route('student.quizzes.take', $quiz->quiz_id) }}" class="btn btn-sm btn-primary fw-bold">
                                Take Quiz
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted">No quizzes posted yet.</p>
            @endforelse
        </div>

        {{-- EXAMS --}}
        <div class="tab-pane fade" id="pane-exams">
            @forelse ($course->exams as $exam)
                @php $sub = $examSubmissions[$exam->exam_id] ?? null; @endphp
                <div class="card mb-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-1">{{ $exam->title }}</h6>
                            <span class="text-muted" style="font-size:12px;">{{ $exam->questions->count() }} questions</span>
                        </div>
                        @if (! $exam->is_published)
                            <span class="badge bg-light text-dark border">Not yet available</span>
                        @elseif ($sub)
                            <a href="{{ route('student.exams.take', $exam->exam_id) }}" class="btn btn-sm btn-outline-primary fw-bold">
                                View Result ({{ $sub->score }}/{{ $sub->max_score }})
                            </a>
                        @else
                            <a href="{{ route('student.exams.take', $exam->exam_id) }}" class="btn btn-sm btn-primary fw-bold">
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

@push('styles')
    <style>
        /* ── EDITH Lesson Card ─────────────────────────────────────────── */
        .edith-lesson-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        .edith-lesson-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px; height: 26px;
            background: #10a37f;
            color: #fff;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* ── Rendered Markdown (ChatGPT-style) ─────────────────────────── */
        .edith-content {
            font-size: 14.5px;
            line-height: 1.75;
            color: #1a1a1a;
        }
        .edith-content h2 {
            font-size: 15px;
            font-weight: 700;
            margin: 1.4em 0 .5em;
            color: #111;
            padding-bottom: 4px;
            border-bottom: 1px solid #f0f0f0;
        }
        .edith-content h3 {
            font-size: 14px;
            font-weight: 700;
            margin: 1.1em 0 .4em;
            color: #222;
        }
        .edith-content p {
            margin: 0 0 .85em;
        }
        .edith-content ul, .edith-content ol {
            padding-left: 1.5em;
            margin: 0 0 .85em;
        }
        .edith-content li {
            margin-bottom: .3em;
        }
        .edith-content li > ul, .edith-content li > ol {
            margin-top: .25em;
        }
        .edith-content strong {
            font-weight: 700;
            color: #111;
        }
        .edith-content em {
            color: #555;
        }
        .edith-content code {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 1px 5px;
            font-size: 13px;
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', monospace;
            color: #c7254e;
        }
        .edith-content pre {
            background: #f6f8fa;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 16px;
            overflow-x: auto;
            margin-bottom: .85em;
        }
        .edith-content pre code {
            background: none;
            border: none;
            padding: 0;
            color: #24292e;
            font-size: 13px;
        }
        .edith-content blockquote {
            border-left: 3px solid #10a37f;
            background: #f0fdf8;
            margin: 0 0 .85em;
            padding: 10px 16px;
            border-radius: 0 6px 6px 0;
            color: #065f46;
            font-size: 13.5px;
        }
        .edith-content blockquote p { margin: 0; }
        .edith-content table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: .85em;
            font-size: 13.5px;
        }
        .edith-content th {
            background: #f9fafb;
            font-weight: 700;
            text-align: left;
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            color: #374151;
        }
        .edith-content td {
            padding: 7px 12px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }
        .edith-content tr:nth-child(even) td { background: #f9fafb; }
        .edith-content hr {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 1.2em 0;
        }
        /* First h2 gets no top margin */
        .edith-content > h2:first-child { margin-top: 0; }

        /* ── Lesson editor: split view ─────────────────────────────────── */
        .editor-split { display: flex; gap: 0; height: 100%; }
        .editor-split-pane { flex: 1; min-width: 0; display: flex; flex-direction: column; }
        .editor-split-pane + .editor-split-pane { border-left: 1px solid #dee2e6; }
        .editor-preview-box {
            flex: 1; overflow-y: auto;
            padding: 20px 24px;
            background: #fff;
            font-size: 14.5px;
        }
        .editor-preview-label {
            padding: 8px 16px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            letter-spacing: .05em;
            text-transform: uppercase;
        }
    </style>
@endpush

@push('scripts')
    @include('student.classes.partials.activity-scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/9.1.6/marked.min.js"></script>
    <script>
        // Render all .edith-content divs from their data-raw attribute
        document.addEventListener('DOMContentLoaded', function () {
            marked.setOptions({
                breaks: true,
                gfm: true,
            });
            document.querySelectorAll('.edith-content[data-raw]').forEach(function (el) {
                const raw = el.getAttribute('data-raw');
                if (raw && raw.trim()) {
                    el.innerHTML = marked.parse(raw);
                } else {
                    el.innerHTML = '<p class="text-muted fst-italic">No content yet.</p>';
                }
            });
        });
    </script>
@endpush
