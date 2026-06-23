@extends('layouts.app')

@section('title', $course->title)

@section('sidebar-nav')
    @include('teacher.partials.sidebar-nav')
@endsection

@section('page-heading')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1">{{ $course->title }}</h3>
            <span class="badge {{ $course->status === 'published' ? 'bg-success' : 'bg-light text-dark border' }}">
                {{ ucfirst($course->status) }}
            </span>
            @if ($course->classRoom)
                <span class="badge bg-light text-dark border">{{ $course->classRoom->class_name }}</span>
            @endif
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary font-bold" data-bs-toggle="modal" data-bs-target="#editOutlineModal">
                <i class="bi bi-pencil me-1"></i> Edit Outline
            </button>
            <form action="{{ route('teacher.courses.publish', $course->course_id) }}" method="POST">
                @csrf
                <button type="submit" class="btn {{ $course->status === 'published' ? 'btn-outline-secondary' : 'btn-success' }} font-bold">
                    {{ $course->status === 'published' ? 'Unpublish' : 'Publish Course' }}
                </button>
            </form>
        </div>
    </div>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if ($course->description)
        <p class="text-muted">{{ $course->description }}</p>
    @endif

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pane-lessons">
                <i class="bi bi-journal-text me-1"></i> Lessons / Topics
                <span class="badge bg-light text-dark border ms-1">{{ $course->lessons->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-activities">
                <i class="bi bi-clipboard-check me-1"></i> Activities
                <span class="badge bg-light text-dark border ms-1">{{ $course->activities->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-quizzes">
                <i class="bi bi-patch-question me-1"></i> Quizzes
                <span class="badge bg-light text-dark border ms-1">{{ $course->quizzes->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-exams">
                <i class="bi bi-file-text me-1"></i> Exams
                <span class="badge bg-light text-dark border ms-1">{{ $course->exams->count() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ============================== LESSONS / TOPICS ============================== --}}
        <div class="tab-pane fade show active" id="pane-lessons">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary font-bold" data-bs-toggle="modal" data-bs-target="#addLessonModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Topic
                </button>
            </div>

            @forelse ($course->lessons as $lesson)
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="font-bold mb-1">
                                {{ $loop->iteration }}. {{ $lesson->title }}
                                @if ($lesson->ai_generated)
                                    <span class="badge bg-light text-primary border" style="font-size:10px;"><i class="bi bi-stars"></i> AI</span>
                                @endif
                            </h6>
                            <div class="d-flex gap-1">
                                {{-- Edit button opens the fullscreen modal --}}
                                <button class="btn btn-sm btn-outline-primary lesson-edit-btn"
                                        data-module-id="{{ $lesson->module_id }}"
                                        data-title="{{ e($lesson->title) }}"
                                        data-content="{{ e($lesson->content ?? '') }}"
                                        data-ai="{{ $lesson->ai_generated ? '1' : '0' }}">
                                    <i class="bi bi-pencil me-1"></i>{{ $lesson->content ? 'Edit Content' : 'Write / Generate' }}
                                </button>
                                {{-- Delete button --}}
                                <form action="{{ route('teacher.courses.modules.destroy', [$course->course_id, $lesson->module_id]) }}" method="POST"
                                      data-confirm="Delete lesson {{ e($lesson->title) }}? This cannot be undone."
                                      onsubmit="return confirm(this.dataset.confirm)">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                        <p class="text-muted mb-0" style="font-size:13px;">
                            {{ $lesson->content ? \Illuminate\Support\Str::limit($lesson->content, 160) : 'No content yet.' }}
                        </p>

                        <div class="collapse mt-3" id="lessonEdit{{ $lesson->module_id }}">
                            <form action="{{ route('teacher.courses.modules.update', [$course->course_id, $lesson->module_id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="title" value="{{ $lesson->title }}">
                                <input type="hidden" name="ai_generated" id="ai-generated-{{ $lesson->module_id }}" value="{{ $lesson->ai_generated ? 1 : 0 }}">

                                <button type="button" class="btn btn-outline-primary btn-sm mb-2"
                                        onclick="generateLessonContent({{ $lesson->module_id }}, {{ \Illuminate\Support\Js::from($lesson->title) }})">
                                    <i class="bi bi-stars me-1"></i> Generate with AI
                                </button>
                                <span id="lesson-status-{{ $lesson->module_id }}" class="text-muted ms-2" style="font-size:12px;"></span>

                                <textarea name="content" id="content-{{ $lesson->module_id }}" class="form-control mb-2" rows="6">{{ $lesson->content }}</textarea>

                                <button type="submit" class="btn btn-primary btn-sm font-bold">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No topics yet. Add one above, or generate an outline from the create-course page next time.</p>
            @endforelse
        </div>

        {{-- ============================== ACTIVITIES ============================== --}}
        <div class="tab-pane fade" id="pane-activities">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary font-bold" data-bs-toggle="modal" data-bs-target="#addActivityModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Activity
                </button>
            </div>

            @forelse ($course->activities as $activity)
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <h6 class="font-bold mb-0">
                                    {{ $activity->title }}
                                    @if ($activity->ai_generated)
                                        <span class="badge bg-light text-primary border" style="font-size:10px;"><i class="bi bi-stars"></i> AI</span>
                                    @endif
                                </h6>
                                <span class="badge bg-light text-dark border">{{ ucfirst($activity->activity_type) }} • {{ $activity->points }} pts</span>
                            </div>
                            <div class="d-flex gap-1 align-items-start">
                                <button class="btn btn-sm btn-outline-primary activity-edit-btn"
                                        data-module-id="{{ $activity->module_id }}"
                                        data-title="{{ e($activity->title) }}"
                                        data-content="{{ e($activity->content ?? '') }}"
                                        data-type="{{ $activity->activity_type }}"
                                        data-points="{{ (int)$activity->points }}"
                                        data-due="{{ $activity->due_at ? $activity->due_at->format('Y-m-d\TH:i') : '' }}">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                                <form action="{{ route('teacher.courses.modules.destroy', [$course->course_id, $activity->module_id]) }}" method="POST"
                                      data-confirm="Delete activity {{ e($activity->title) }}?"
                                      onsubmit="return confirm(this.dataset.confirm)">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                        <p class="text-muted mb-0" style="font-size:13px;">{{ \Illuminate\Support\Str::limit($activity->content, 160) }}</p>
                        @if ($activity->due_at)
                            <p class="text-muted mb-0" style="font-size:12px;"><i class="bi bi-clock"></i> Due {{ $activity->due_at->format('M j, Y g:i A') }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted">No activities yet.</p>
            @endforelse
        </div>

        {{-- ============================== QUIZZES ============================== --}}
        <div class="tab-pane fade" id="pane-quizzes">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary font-bold" data-bs-toggle="modal" data-bs-target="#addQuizModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Quiz
                </button>
            </div>

            @forelse ($course->quizzes as $quiz)
                <div class="card mb-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="font-bold mb-1">
                                {{ $quiz->title }}
                                @if ($quiz->ai_generated)
                                    <span class="badge bg-light text-primary border" style="font-size:10px;"><i class="bi bi-stars"></i> AI</span>
                                @endif
                                <span class="badge {{ $quiz->is_published ? 'bg-success' : 'bg-light text-dark border' }} ms-1" style="font-size:10px;">
                                    {{ $quiz->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </h6>
                            <span class="text-muted" style="font-size:12px;">{{ $quiz->questions->count() }} questions</span>
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary font-bold assessment-edit-btn"
                                    data-kind="quiz"
                                    data-id="{{ $quiz->quiz_id }}"
                                    data-title="{{ e($quiz->title) }}"
                                    data-description="{{ e($quiz->description ?? '') }}"
                                    data-questions="{{ e($quiz->questions->load('choices')->toJson()) }}"
                                    data-update-url="{{ route('teacher.courses.quizzes.update', [$course->course_id, $quiz->quiz_id]) }}">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </button>
                            <form action="{{ route('teacher.courses.quizzes.publish', [$course->course_id, $quiz->quiz_id]) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $quiz->is_published ? 'btn-outline-secondary' : 'btn-success' }} font-bold">
                                    {{ $quiz->is_published ? 'Unpublish' : 'Publish' }}
                                </button>
                            </form>
                            <form action="{{ route('teacher.courses.quizzes.destroy', [$course->course_id, $quiz->quiz_id]) }}" method="POST"
                                  data-confirm="Delete quiz {{ e($quiz->title) }}? All student attempts will also be removed."
                                  onsubmit="return confirm(this.dataset.confirm)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No quizzes yet.</p>
            @endforelse
        </div>

        {{-- ============================== EXAMS ============================== --}}
        <div class="tab-pane fade" id="pane-exams">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary font-bold" data-bs-toggle="modal" data-bs-target="#addExamModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Exam
                </button>
            </div>

            @forelse ($course->exams as $exam)
                <div class="card mb-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="font-bold mb-1">
                                {{ $exam->title }}
                                @if ($exam->ai_generated)
                                    <span class="badge bg-light text-primary border" style="font-size:10px;"><i class="bi bi-stars"></i> AI</span>
                                @endif
                                <span class="badge {{ $exam->is_published ? 'bg-success' : 'bg-light text-dark border' }} ms-1" style="font-size:10px;">
                                    {{ $exam->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </h6>
                            <span class="text-muted" style="font-size:12px;">{{ $exam->questions->count() }} questions</span>
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary font-bold assessment-edit-btn"
                                    data-kind="exam"
                                    data-id="{{ $exam->exam_id }}"
                                    data-title="{{ e($exam->title) }}"
                                    data-description="{{ e($exam->description ?? '') }}"
                                    data-questions="{{ e($exam->questions->load('choices')->toJson()) }}"
                                    data-update-url="{{ route('teacher.courses.exams.update', [$course->course_id, $exam->exam_id]) }}">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </button>
                            <form action="{{ route('teacher.courses.exams.publish', [$course->course_id, $exam->exam_id]) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $exam->is_published ? 'btn-outline-secondary' : 'btn-success' }} font-bold">
                                    {{ $exam->is_published ? 'Unpublish' : 'Publish' }}
                                </button>
                            </form>
                            <form action="{{ route('teacher.courses.exams.destroy', [$course->course_id, $exam->exam_id]) }}" method="POST"
                                  data-confirm="Delete exam {{ e($exam->title) }}? All student attempts will also be removed."
                                  onsubmit="return confirm(this.dataset.confirm)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No exams yet.</p>
            @endforelse
        </div>

    </div>

    {{-- ============================= EDIT OUTLINE MODAL ============================= --}}
    <div class="modal fade" id="editOutlineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('teacher.courses.outline.update', $course->course_id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title font-bold"><i class="bi bi-pencil me-1"></i> Edit Course Outline</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label font-bold">Course Title</label>
                            <input type="text" name="title" class="form-control" required value="{{ $course->title }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-bold">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ $course->description }}</textarea>
                        </div>
                        <div class="mb-1">
                            <label class="form-label font-bold">Learning Objectives <span class="text-muted fw-normal">(one per line)</span></label>
                            <textarea name="learning_objectives" class="form-control" rows="4">{{ $course->learning_objectives }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary font-bold">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================= FULLSCREEN LESSON EDITOR MODAL ============================= --}}
    <div class="modal fade" id="lessonEditorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <form id="lesson-editor-form" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="ai_generated" id="editor-ai-generated" value="0">

                    <div class="modal-header" style="background:#f8f9fa; border-bottom:1px solid #dee2e6;">
                        <div class="d-flex align-items-center gap-3 w-100">
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            <h5 class="modal-title font-bold mb-0" id="editor-lesson-title-display">Edit Lesson</h5>
                            <span id="editor-status" class="text-muted ms-2" style="font-size:12px;"></span>
                            <div class="ms-auto d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm font-bold"
                                        onclick="generateLessonContentFromEditor()">
                                    <i class="bi bi-stars me-1"></i> Regenerate with AI
                                </button>
                                <button type="submit" class="btn btn-primary btn-sm font-bold">
                                    <i class="bi bi-floppy me-1"></i> Save
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="modal-body p-0 d-flex flex-column" style="height:calc(100vh - 60px);">
                        <div class="px-4 pt-3 pb-2" style="background:#fff; border-bottom:1px solid #eee;">
                            <label class="form-label font-bold mb-1" style="font-size:13px;">Lesson Title</label>
                            <input type="text" name="title" id="editor-lesson-title-input"
                                   class="form-control" style="max-width:600px;" required>
                        </div>
                        <div class="flex-grow-1 p-4 d-flex flex-column" style="min-height:0;">
                            <label class="form-label font-bold mb-1" style="font-size:13px;">Content</label>
                            <textarea name="content" id="editor-content"
                                      class="form-control flex-grow-1"
                                      style="resize:none; font-size:14px; line-height:1.7; font-family:inherit;"
                                      required></textarea>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================= EDIT ACTIVITY MODAL ============================= --}}
    <div class="modal fade" id="editActivityModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="edit-activity-form" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="item_type" value="activity">
                    <div class="modal-header">
                        <h5 class="modal-title font-bold">Edit Activity</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label font-bold">Title</label>
                            <input type="text" name="title" id="edit-activity-title" class="form-control" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label font-bold">Type</label>
                                <select name="activity_type" id="edit-activity-type" class="form-control">
                                    <option value="assignment">Assignment</option>
                                    <option value="discussion">Discussion</option>
                                    <option value="project">Project</option>
                                    <option value="reflection">Reflection</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-bold">Points</label>
                                <input type="number" name="points" id="edit-activity-points" class="form-control" min="1" max="100">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-bold">Due (optional)</label>
                                <input type="datetime-local" name="due_at" id="edit-activity-due" class="form-control">
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label font-bold">Instructions</label>
                            <textarea name="content" id="edit-activity-content" class="form-control" rows="7" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary font-bold">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================= EDIT QUIZ / EXAM MODAL ============================= --}}
    <div class="modal fade" id="editAssessmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <form id="edit-assessment-form" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title font-bold" id="editAssessmentModalLabel">Edit Quiz</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label font-bold">Title</label>
                            <input type="text" name="title" id="edit-assessment-title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-bold">Description <span class="text-muted fw-normal">(optional)</span></label>
                            <textarea name="description" id="edit-assessment-description" class="form-control" rows="2"></textarea>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-bold mb-0">Questions</h6>
                            <button type="button" class="btn btn-outline-primary btn-sm font-bold"
                                    onclick="addQuestionRow('edit-assessment-questions-container')">
                                <i class="bi bi-plus-circle me-1"></i> Add Question
                            </button>
                        </div>
                        <div id="edit-assessment-questions-container"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary font-bold"><i class="bi bi-floppy me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('teacher.courses.partials.add-lesson-modal')
    @include('teacher.courses.partials.add-activity-modal')
    @include('teacher.courses.partials.add-assessment-modal', ['kind' => 'quiz', 'modalId' => 'addQuizModal', 'storeRoute' => route('teacher.courses.quizzes.store', $course->course_id)])
    @include('teacher.courses.partials.add-assessment-modal', ['kind' => 'exam', 'modalId' => 'addExamModal', 'storeRoute' => route('teacher.courses.exams.store', $course->course_id)])

    <template id="question-row-template">
        <div class="question-row border rounded p-3 mb-2">
            <div class="d-flex justify-content-between mb-2">
                <strong style="font-size:13px;">Question</strong>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.question-row').remove()">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <textarea class="form-control mb-2 q-text" rows="2" placeholder="Question text" required></textarea>
            <div class="row mb-2">
                <div class="col-7">
                    <select class="form-control q-type" onchange="toggleQuestionType(this)">
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="open_ended">Open-ended</option>
                    </select>
                </div>
                <div class="col-5">
                    <input type="number" class="form-control q-points" placeholder="Points" value="1" min="1" max="100">
                </div>
            </div>
            <div class="mc-choices">
                <p class="text-muted mb-1" style="font-size:12px;">Select the radio next to the correct choice:</p>
                <div class="d-flex align-items-center mb-1">
                    <input type="radio" class="me-2 q-correct-radio">
                    <input type="text" class="form-control choice-text" placeholder="Choice A">
                </div>
                <div class="d-flex align-items-center mb-1">
                    <input type="radio" class="me-2 q-correct-radio">
                    <input type="text" class="form-control choice-text" placeholder="Choice B">
                </div>
                <div class="d-flex align-items-center mb-1">
                    <input type="radio" class="me-2 q-correct-radio">
                    <input type="text" class="form-control choice-text" placeholder="Choice C">
                </div>
                <div class="d-flex align-items-center mb-1">
                    <input type="radio" class="me-2 q-correct-radio">
                    <input type="text" class="form-control choice-text" placeholder="Choice D">
                </div>
            </div>
            <div class="open-answer" style="display:none;">
                <textarea class="form-control model-answer" rows="2" placeholder="Model answer (for your reference when grading)"></textarea>
            </div>
        </div>
    </template>

@endsection

@push('scripts')
    <script>
        function csrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        // ===================== FULLSCREEN LESSON EDITOR =====================
        let currentEditorModuleId = null;

        function openLessonEditor(moduleId, title, content, aiGenerated) {
            currentEditorModuleId = moduleId;
            const baseUrl = '{{ url('teacher/courses/' . $course->course_id . '/modules') }}';

            document.getElementById('lesson-editor-form').action = `${baseUrl}/${moduleId}`;
            document.getElementById('editor-lesson-title-display').textContent = title;
            document.getElementById('editor-lesson-title-input').value = title;
            document.getElementById('editor-content').value = content || '';
            document.getElementById('editor-ai-generated').value = aiGenerated ? '1' : '0';
            document.getElementById('editor-status').textContent = '';

            const modal = new bootstrap.Modal(document.getElementById('lessonEditorModal'));
            modal.show();
        }

        // Delegate click for lesson edit buttons (avoids inline JS with special chars)
        document.addEventListener('click', function (e) {
            const lessonBtn = e.target.closest('.lesson-edit-btn');
            if (lessonBtn) {
                openLessonEditor(
                    lessonBtn.dataset.moduleId,
                    lessonBtn.dataset.title,
                    lessonBtn.dataset.content,
                    lessonBtn.dataset.ai === '1'
                );
            }

            const activityBtn = e.target.closest('.activity-edit-btn');
            if (activityBtn) {
                openActivityEditor(
                    activityBtn.dataset.moduleId,
                    activityBtn.dataset.title,
                    activityBtn.dataset.content,
                    activityBtn.dataset.type,
                    activityBtn.dataset.points,
                    activityBtn.dataset.due
                );
            }

            const assessmentBtn = e.target.closest('.assessment-edit-btn');
            if (assessmentBtn) {
                openAssessmentEditor(assessmentBtn);
            }
        });

        // ===================== EDIT QUIZ / EXAM MODAL =====================
        function openAssessmentEditor(btn) {
            const kind        = btn.dataset.kind;  // 'quiz' or 'exam'
            const title       = btn.dataset.title;
            const description = btn.dataset.description;
            const updateUrl   = btn.dataset.updateUrl;
            const questions   = JSON.parse(btn.dataset.questions);

            document.getElementById('editAssessmentModalLabel').textContent = `Edit ${kind.charAt(0).toUpperCase() + kind.slice(1)}`;
            document.getElementById('edit-assessment-form').action = updateUrl;
            document.getElementById('edit-assessment-title').value = title;
            document.getElementById('edit-assessment-description').value = description;

            // Reset and populate questions
            const containerId = 'edit-assessment-questions-container';
            resetQuestions(containerId);

            questions.forEach(q => {
                const row = addQuestionRow(containerId);
                row.querySelector('.q-text').value   = q.question_text || '';
                row.querySelector('.q-points').value = q.points || 1;
                const typeSelect = row.querySelector('.q-type');
                typeSelect.value = q.question_type === 'open_ended' ? 'open_ended' : 'multiple_choice';
                toggleQuestionType(typeSelect);

                if (typeSelect.value === 'multiple_choice') {
                    const choiceInputs = row.querySelectorAll('.choice-text');
                    const radios       = row.querySelectorAll('.q-correct-radio');
                    (q.choices || []).forEach((choice, j) => {
                        if (choiceInputs[j]) choiceInputs[j].value = choice.choice_text || '';
                        if (choice.is_correct && radios[j]) radios[j].checked = true;
                    });
                } else {
                    row.querySelector('.model-answer').value = q.correct_answer || '';
                }
            });

            const modal = new bootstrap.Modal(document.getElementById('editAssessmentModal'));
            modal.show();
        }

        async function generateLessonContentFromEditor() {
            const title  = document.getElementById('editor-lesson-title-input').value.trim();
            const status = document.getElementById('editor-status');
            const textarea = document.getElementById('editor-content');
            if (!title) { status.textContent = 'Enter a title first.'; return; }

            status.textContent = 'Generating with Gemma 4...';
            try {
                const res = await fetch('{{ route('teacher.courses.ai.lesson-content', $course->course_id) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                    body: JSON.stringify({ title: title, summary: textarea.value }),
                });
                const result = await res.json();
                if (!result.success) { status.textContent = result.message || 'Generation failed.'; return; }
                textarea.value = result.data.content || '';
                document.getElementById('editor-ai-generated').value = '1';
                status.textContent = 'Draft generated — edit as needed, then Save.';
            } catch (e) {
                status.textContent = 'Something went wrong reaching the AI service.';
            }
        }

        // ===================== EDIT ACTIVITY MODAL =====================
        function openActivityEditor(moduleId, title, content, activityType, points, dueAt) {
            const baseUrl = '{{ url('teacher/courses/' . $course->course_id . '/modules') }}';
            document.getElementById('edit-activity-form').action = `${baseUrl}/${moduleId}`;
            document.getElementById('edit-activity-title').value  = title;
            document.getElementById('edit-activity-content').value = content || '';
            document.getElementById('edit-activity-type').value   = activityType;
            document.getElementById('edit-activity-points').value = points;
            document.getElementById('edit-activity-due').value    = dueAt || '';

            const modal = new bootstrap.Modal(document.getElementById('editActivityModal'));
            modal.show();
        }

        // ===================== NEW LESSON MODAL AI =====================
        async function generateNewLessonContent() {
            const title  = document.getElementById('new-lesson-title').value.trim();
            const status = document.getElementById('new-lesson-status');
            if (!title) { status.textContent = 'Enter a title first.'; return; }

            status.textContent = 'Generating with Gemma 4...';
            try {
                const res = await fetch('{{ route('teacher.courses.ai.lesson-content', $course->course_id) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                    body: JSON.stringify({ title: title }),
                });
                const result = await res.json();
                if (!result.success) { status.textContent = result.message || 'Generation failed.'; return; }
                document.getElementById('new-lesson-content').value = result.data.content || '';
                document.getElementById('new-lesson-ai-generated').value = '1';
                status.textContent = 'Draft generated — edit as needed, then Save.';
            } catch (e) {
                status.textContent = 'Something went wrong reaching the AI service.';
            }
        }

        // ===================== NEW ACTIVITY MODAL AI =====================
        async function generateNewActivity() {
            const topic  = document.getElementById('new-activity-topic').value.trim();
            const status = document.getElementById('new-activity-status');
            if (!topic) { status.textContent = 'Enter a topic first.'; return; }

            status.textContent = 'Generating with Gemma 4...';
            try {
                const res = await fetch('{{ route('teacher.courses.ai.activity', $course->course_id) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                    body: JSON.stringify({ topic: topic }),
                });
                const result = await res.json();
                if (!result.success) { status.textContent = result.message || 'Generation failed.'; return; }
                const data = result.data;
                document.getElementById('new-activity-title').value = data.title || '';
                document.getElementById('new-activity-type').value  = data.activity_type || 'assignment';
                document.getElementById('new-activity-content').value = data.content || '';
                document.getElementById('new-activity-points').value  = data.points || 10;
                document.getElementById('new-activity-ai-generated').value = '1';
                status.textContent = 'Draft generated — edit as needed, then Save.';
            } catch (e) {
                status.textContent = 'Something went wrong reaching the AI service.';
            }
        }

        // ===================== QUIZ / EXAM QUESTION BUILDER =====================
        const questionIndexByContainer = {};

        function addQuestionRow(containerId) {
            const container = document.getElementById(containerId);
            const template  = document.getElementById('question-row-template');
            const node      = template.content.cloneNode(true);
            const row       = node.querySelector('.question-row');

            const i = questionIndexByContainer[containerId] = (questionIndexByContainer[containerId] || 0);
            questionIndexByContainer[containerId] = i + 1;

            row.querySelector('.q-text').name    = `questions[${i}][question_text]`;
            row.querySelector('.q-type').name    = `questions[${i}][question_type]`;
            row.querySelector('.q-points').name  = `questions[${i}][points]`;
            row.querySelector('.model-answer').name = `questions[${i}][correct_answer]`;

            row.querySelectorAll('.q-correct-radio').forEach((radio, j) => {
                radio.name  = `questions[${i}][correct_choice]`;
                radio.value = j;
            });
            row.querySelectorAll('.choice-text').forEach((input, j) => {
                input.name = `questions[${i}][choices][${j}][text]`;
            });

            container.appendChild(row);
            return container.lastElementChild;
        }

        function toggleQuestionType(select) {
            const row  = select.closest('.question-row');
            const isMc = select.value === 'multiple_choice';
            row.querySelector('.mc-choices').style.display  = isMc ? 'block' : 'none';
            row.querySelector('.open-answer').style.display = isMc ? 'none'  : 'block';
        }

        function resetQuestions(containerId) {
            document.getElementById(containerId).innerHTML = '';
            questionIndexByContainer[containerId] = 0;
        }

        async function generateAssessment(kind) {
            const prefix = kind; // 'quiz' or 'exam'
            const status = document.getElementById(`new-${prefix}-status`);
            const numQuestions = document.getElementById(`new-${prefix}-num-questions`).value || 5;

            // Quiz needs a specific topic. Exam covers ALL topics — no topic input needed.
            let topic = '';
            if (kind === 'quiz') {
                const topicInput = document.getElementById(`new-${prefix}-topic`);
                topic = topicInput ? topicInput.value.trim() : '';
                if (!topic) { status.textContent = 'Enter a topic first.'; return; }
            }

            status.textContent = 'Generating with AI...';

            try {
                const payload = { kind: kind, num_questions: numQuestions };
                if (topic) payload.topic = topic;

                const res = await fetch('{{ route('teacher.courses.ai.assessment', $course->course_id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload),
                });

                const result = await res.json();
                if (!result.success) { status.textContent = result.message || 'Generation failed.'; return; }

                const data = result.data;
                const containerId = `${kind}-questions-container`;
                document.getElementById(`new-${kind}-title`).value = data.title || '';
                resetQuestions(containerId);

                (data.questions || []).forEach(q => {
                    const row = addQuestionRow(containerId);
                    row.querySelector('.q-text').value   = q.question_text || '';
                    row.querySelector('.q-points').value = q.points || 1;
                    const typeSelect = row.querySelector('.q-type');
                    typeSelect.value = q.question_type === 'open_ended' ? 'open_ended' : 'multiple_choice';
                    toggleQuestionType(typeSelect);

                    if (typeSelect.value === 'multiple_choice') {
                        const choiceInputs = row.querySelectorAll('.choice-text');
                        const radios       = row.querySelectorAll('.q-correct-radio');
                        (q.choices || []).forEach((choice, j) => {
                            if (choiceInputs[j]) choiceInputs[j].value = choice.choice_text || '';
                            if (choice.is_correct && radios[j]) radios[j].checked = true;
                        });
                    } else {
                        row.querySelector('.model-answer').value = q.correct_answer || '';
                    }
                });

                document.getElementById(`new-${prefix}-ai-generated`).value = '1';
                status.textContent = `Draft generated — ${data.questions?.length || 0} questions. Review each one, then Save.`;
            } catch (e) {
                status.textContent = 'Something went wrong reaching the AI service.';
            }
        }
    </script>
    document.getElementById(`new-${kind}-ai-generated`).value = '1';
    status.textContent = 'Draft generated — review/edit each question, then Save.';
    } catch (e) {
    status.textContent = 'Something went wrong reaching the AI service.';
    }
    }
    </script>
@endpush
