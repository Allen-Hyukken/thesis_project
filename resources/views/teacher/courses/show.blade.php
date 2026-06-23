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
        <form action="{{ route('teacher.courses.publish', $course->course_id) }}" method="POST">
            @csrf
            <button type="submit" class="btn {{ $course->status === 'published' ? 'btn-outline-secondary' : 'btn-success' }} font-bold">
                {{ $course->status === 'published' ? 'Unpublish' : 'Publish Course' }}
            </button>
        </form>
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
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#lessonEdit{{ $lesson->module_id }}">
                                {{ $lesson->content ? 'Edit Content' : 'Write / Generate Content' }}
                            </button>
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
                            <h6 class="font-bold mb-0">
                                {{ $activity->title }}
                                @if ($activity->ai_generated)
                                    <span class="badge bg-light text-primary border" style="font-size:10px;"><i class="bi bi-stars"></i> AI</span>
                                @endif
                            </h6>
                            <span class="badge bg-light text-dark border">{{ ucfirst($activity->activity_type) }} • {{ $activity->points }} pts</span>
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
                            </h6>
                            <span class="text-muted" style="font-size:12px;">{{ $quiz->questions->count() }} questions</span>
                        </div>
                        <form action="{{ route('teacher.courses.quizzes.publish', [$course->course_id, $quiz->quiz_id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $quiz->is_published ? 'btn-outline-secondary' : 'btn-success' }} font-bold">
                                {{ $quiz->is_published ? 'Unpublish' : 'Publish' }}
                            </button>
                        </form>
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
                            </h6>
                            <span class="text-muted" style="font-size:12px;">{{ $exam->questions->count() }} questions</span>
                        </div>
                        <form action="{{ route('teacher.courses.exams.publish', [$course->course_id, $exam->exam_id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $exam->is_published ? 'btn-outline-secondary' : 'btn-success' }} font-bold">
                                {{ $exam->is_published ? 'Unpublish' : 'Publish' }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-muted">No exams yet.</p>
            @endforelse
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

// ---------- Lessons: generate/regenerate content for an EXISTING topic ----------
async function generateLessonContent(moduleId, title) {
    const status = document.getElementById('lesson-status-' + moduleId);
    const textarea = document.getElementById('content-' + moduleId);
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
        document.getElementById('ai-generated-' + moduleId).value = '1';
        status.textContent = 'Draft generated — edit as needed, then Save.';
    } catch (e) {
        status.textContent = 'Something went wrong reaching the AI service.';
    }
}

// ---------- New Lesson modal AI helper ----------
async function generateNewLessonContent() {
    const title = document.getElementById('new-lesson-title').value.trim();
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

// ---------- New Activity modal AI helper ----------
async function generateNewActivity() {
    const topic = document.getElementById('new-activity-topic').value.trim();
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
        document.getElementById('new-activity-type').value = data.activity_type || 'assignment';
        document.getElementById('new-activity-content').value = data.content || '';
        document.getElementById('new-activity-points').value = data.points || 10;
        document.getElementById('new-activity-ai-generated').value = '1';
        status.textContent = 'Draft generated — edit as needed, then Save.';
    } catch (e) {
        status.textContent = 'Something went wrong reaching the AI service.';
    }
}

// ---------- Quiz / Exam question builder (shared by both modals) ----------
const questionIndexByContainer = {};

function addQuestionRow(containerId) {
    const container = document.getElementById(containerId);
    const template = document.getElementById('question-row-template');
    const node = template.content.cloneNode(true);
    const row = node.querySelector('.question-row');

    const i = questionIndexByContainer[containerId] = (questionIndexByContainer[containerId] || 0);
    questionIndexByContainer[containerId] = i + 1;

    row.querySelector('.q-text').name = `questions[${i}][question_text]`;
    row.querySelector('.q-type').name = `questions[${i}][question_type]`;
    row.querySelector('.q-points').name = `questions[${i}][points]`;
    row.querySelector('.model-answer').name = `questions[${i}][correct_answer]`;

    row.querySelectorAll('.q-correct-radio').forEach((radio, j) => {
        radio.name = `questions[${i}][correct_choice]`;
        radio.value = j;
    });
    row.querySelectorAll('.choice-text').forEach((input, j) => {
        input.name = `questions[${i}][choices][${j}][text]`;
    });

    container.appendChild(row);
    return container.lastElementChild;
}

function toggleQuestionType(select) {
    const row = select.closest('.question-row');
    const isMc = select.value === 'multiple_choice';
    row.querySelector('.mc-choices').style.display = isMc ? 'block' : 'none';
    row.querySelector('.open-answer').style.display = isMc ? 'none' : 'block';
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
        const containerId = `${prefix}-questions-container`;
        document.getElementById(`new-${prefix}-title`).value = data.title || '';
        resetQuestions(containerId);

        (data.questions || []).forEach(q => {
            const row = addQuestionRow(containerId);
            row.querySelector('.q-text').value = q.question_text || '';
            row.querySelector('.q-points').value = q.points || 1;
            const typeSelect = row.querySelector('.q-type');
            typeSelect.value = q.question_type === 'open_ended' ? 'open_ended' : 'multiple_choice';
            toggleQuestionType(typeSelect);

            if (typeSelect.value === 'multiple_choice') {
                const choiceInputs = row.querySelectorAll('.choice-text');
                const radios = row.querySelectorAll('.q-correct-radio');
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
@endpush
