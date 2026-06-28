@php
    $label = $kind === 'exam' ? 'Exam' : 'Quiz';
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ $storeRoute }}" method="POST">
                @csrf
                <input type="hidden" name="ai_generated" id="new-{{ $kind }}-ai-generated" value="0">

                <div class="modal-header">
                    <h5 class="modal-title font-bold">Add {{ $label }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" style="max-height:70vh;overflow-y:auto;">

                    {{-- AI helper --}}
                    <div class="border rounded p-3 mb-3" style="background:#f8f9ff;">
                        <span class="badge bg-light text-primary fw-bold mb-2" style="font-size:11px;">
                            <i class="bi bi-stars me-1"></i> AI ASSIST (EDITH)
                        </span>

                        @if ($kind === 'quiz')
                            {{-- Quiz: teacher picks from actual lesson topics --}}
                            <div class="row g-2 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label font-bold" style="font-size:13px;">Topic</label>
                                    @if (isset($course) && $course->lessons->count())
                                        <select id="new-{{ $kind }}-topic" class="form-control">
                                            <option value="">— Select a lesson topic —</option>
                                            @foreach ($course->lessons as $lesson)
                                                <option value="{{ $lesson->title }}">{{ $lesson->title }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Quiz questions will be based on the selected lesson.</small>
                                    @else
                                        <input type="text"
                                               id="new-{{ $kind }}-topic"
                                               class="form-control"
                                               placeholder="No lessons yet — type a topic manually">
                                        <small class="text-muted text-warning">Add lessons first to enable topic selection.</small>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label font-bold" style="font-size:13px;"># Questions</label>
                                    <input type="number"
                                           id="new-{{ $kind }}-num-questions"
                                           class="form-control"
                                           min="3" max="15" value="5">
                                </div>
                                <div class="col-md-3">
                                    <button type="button"
                                            class="btn btn-primary w-100 font-bold"
                                            onclick="generateAssessment('{{ $kind }}')">
                                        <i class="bi bi-stars me-1"></i> Generate
                                    </button>
                                </div>
                            </div>
                        @else
                            {{-- Exam: covers ALL topics automatically --}}
                            <div class="alert alert-light border mb-2" style="font-size:13px;">
                                <i class="bi bi-info-circle me-1 text-primary"></i>
                                Exams cover <strong>all topics</strong> in this course. The AI will generate
                                questions from every lesson automatically.
                            </div>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label font-bold" style="font-size:13px;"># Questions</label>
                                    <input type="number"
                                           id="new-{{ $kind }}-num-questions"
                                           class="form-control"
                                           min="3" max="30" value="20">
                                    <small class="text-muted">Max 30 questions for exams.</small>
                                </div>
                                <div class="col-md-6">
                                    <button type="button"
                                            class="btn btn-primary w-100 font-bold"
                                            onclick="generateAssessment('{{ $kind }}')">
                                        <i class="bi bi-stars me-1"></i> Generate Exam
                                    </button>
                                </div>
                            </div>
                        @endif

                        <span id="new-{{ $kind }}-status"
                              class="text-muted d-block mt-1"
                              style="font-size:12px;"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-bold">{{ $label }} Title</label>
                        <input type="text" name="title" id="new-{{ $kind }}-title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-bold">Description (optional)</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>

                    {{-- Link to topic: only for quizzes, not exams --}}
                    @if ($kind === 'quiz' && isset($course) && $course->lessons->count())
                        <div class="mb-3">
                            <label class="form-label font-bold">Link to Topic (optional)</label>
                            <select name="module_id" class="form-control">
                                <option value="">No specific topic</option>
                                @foreach ($course->lessons as $lesson)
                                    <option value="{{ $lesson->module_id }}">{{ $lesson->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label font-bold mb-0">Questions</label>
                        <button type="button"
                                class="btn btn-light btn-sm font-bold"
                                onclick="addQuestionRow('{{ $kind }}-questions-container')">
                            <i class="bi bi-plus-lg me-1"></i> Add Question
                        </button>
                    </div>

                    <div id="{{ $kind }}-questions-container"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-bold">Save {{ $label }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
