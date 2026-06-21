@extends('layouts.app')

@section('title', 'Create Course')

@section('sidebar-nav')
    @include('teacher.partials.sidebar-nav')
@endsection

@section('page-heading')
    <h3>Create Course</h3>
@endsection

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="row">
        <div class="col-12 col-lg-8">

            {{-- AI Outline Assistant --}}
            <div class="card mb-3" style="border:1px solid #c7d2fe;">
                <div class="card-body">
                    <span class="badge bg-light text-primary fw-bold mb-2" style="font-size:11px;">
                        <i class="bi bi-stars me-1"></i> AI ASSIST (Gemma 4)
                    </span>
                    <h5 class="font-bold mb-2">Draft an outline first, then refine it</h5>
                    <p class="text-muted mb-3" style="font-size:13px;">
                        Tell the AI what the course is about. It'll suggest a title, description, objectives,
                        and a list of topics — all editable below before you create anything.
                    </p>

                    <div class="mb-2">
                        <label class="form-label font-bold">Course topic</label>
                        <input type="text" id="ai-topic" class="form-control" placeholder="e.g. Introduction to Data Structures and Algorithms">
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bold">Notes for the AI (optional)</label>
                        <textarea id="ai-notes" class="form-control" rows="2" placeholder="e.g. for 2nd year BSIT students, focus on practical coding examples"></textarea>
                    </div>

                    <button type="button" id="generate-outline-btn" class="btn btn-primary font-bold" onclick="generateOutline()">
                        <i class="bi bi-stars me-1"></i> Generate Outline
                    </button>
                    <span id="outline-status" class="text-muted ms-2" style="font-size:13px;"></span>
                </div>
            </div>

            {{-- Course Form (always here — AI just pre-fills it) --}}
            <form action="{{ route('teacher.courses.store') }}" method="POST" id="course-form">
                @csrf

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label font-bold">Course Title</label>
                            <input type="text" name="title" id="title" class="form-control" required value="{{ old('title') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-bold">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-bold">Learning Objectives</label>
                            <textarea name="learning_objectives" id="learning_objectives" class="form-control" rows="3" placeholder="One per line">{{ old('learning_objectives') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <input type="hidden" name="visibility" value="private">
                                <label class="form-label font-bold">Link to a Class</label>
                                <select name="class_id" class="form-control">
                                    <option value="">No class — unassigned course</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->class_id }}">{{ $class->class_name }} ({{ $class->class_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Modules / Topics --}}
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Lessons / Topics</h4>
                        <button type="button" class="btn btn-light btn-sm font-bold" onclick="addModuleRow()">
                            <i class="bi bi-plus-lg me-1"></i> Add Topic
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="modules-container"></div>
                        <p id="no-modules-msg" class="text-muted mb-0" style="font-size:13px;">
                            No topics yet — generate an outline above, or add one manually. You can write full lesson
                            content for each topic after the course is created.
                        </p>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg font-bold">
                    <i class="bi bi-check-circle me-1"></i> Create Course
                </button>
                <a href="{{ route('teacher.courses') }}" class="btn btn-light btn-lg">Cancel</a>
            </form>

        </div>
    </div>

    <template id="module-row-template">
        <div class="module-row border rounded p-3 mb-2">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="form-check">
                    <input class="form-check-input module-include" type="checkbox" checked onchange="toggleModuleRow(this)">
                    <label class="form-check-label font-bold" style="font-size:13px;">Include</label>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.module-row').remove(); updateEmptyState();">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <input type="text" class="form-control mb-2 module-title" placeholder="Topic title">
            <textarea class="form-control module-content" rows="2" placeholder="Short summary (optional — you'll write/generate full content after creating the course)"></textarea>
            <span class="badge bg-light text-primary border module-ai-badge mt-2" style="display:none;font-size:11px;">
                <i class="bi bi-stars"></i> AI suggested
            </span>
        </div>
    </template>

@endsection

@push('scripts')
<script>
let moduleIndex = 0;

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

function updateEmptyState() {
    const container = document.getElementById('modules-container');
    document.getElementById('no-modules-msg').style.display = container.children.length === 0 ? 'block' : 'none';
}

function addModuleRow(title = '', content = '', aiGenerated = false) {
    const template = document.getElementById('module-row-template');
    const node = template.content.cloneNode(true);
    const row = node.querySelector('.module-row');
    const i = moduleIndex++;

    const titleInput = row.querySelector('.module-title');
    const contentInput = row.querySelector('.module-content');
    titleInput.value = title;
    contentInput.value = content;
    titleInput.name = `modules[${i}][title]`;
    contentInput.name = `modules[${i}][content]`;

    const aiField = document.createElement('input');
    aiField.type = 'hidden';
    aiField.name = `modules[${i}][ai_generated]`;
    aiField.value = aiGenerated ? '1' : '0';
    row.appendChild(aiField);

    if (aiGenerated) {
        row.querySelector('.module-ai-badge').style.display = 'inline-block';
    }

    document.getElementById('modules-container').appendChild(row);
    updateEmptyState();
}

function toggleModuleRow(checkbox) {
    const row = checkbox.closest('.module-row');
    row.querySelectorAll('.module-title, .module-content').forEach(el => {
        el.disabled = !checkbox.checked;
    });
}

async function generateOutline() {
    const topic = document.getElementById('ai-topic').value.trim();
    const notes = document.getElementById('ai-notes').value.trim();
    const status = document.getElementById('outline-status');
    const btn = document.getElementById('generate-outline-btn');

    if (!topic) {
        status.textContent = 'Enter a course topic first.';
        return;
    }

    btn.disabled = true;
    status.textContent = 'Generating with Gemma 4...';

    try {
        const response = await fetch('{{ route('teacher.courses.ai.outline') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ topic, notes }),
        });

        const result = await response.json();

        if (!result.success) {
            status.textContent = result.message || 'Generation failed.';
            btn.disabled = false;
            return;
        }

        const data = result.data;
        document.getElementById('title').value = data.title || '';
        document.getElementById('description').value = data.description || '';
        document.getElementById('learning_objectives').value = data.learning_objectives || '';

        document.getElementById('modules-container').innerHTML = '';
        (data.modules || []).forEach(m => addModuleRow(m.title || '', m.summary || '', true));

        status.textContent = 'Outline generated — review and edit before creating the course.';
    } catch (e) {
        status.textContent = 'Something went wrong reaching the AI service.';
    } finally {
        btn.disabled = false;
    }
}

updateEmptyState();
</script>
@endpush
