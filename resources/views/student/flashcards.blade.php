@extends('layouts.app')

@section('title', $course->title . ' — Flashcards')

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
@endsection

@section('page-heading')
    <div>
        <h3 class="mb-1"><i class="bi bi-collection me-1"></i> Flashcards</h3>
        <a href="{{ route('student.classes.courses.show', [$class->class_id, $course->course_id]) }}" class="text-muted" style="font-size:13px;">
            <i class="bi bi-arrow-left"></i> Back to {{ $course->title }}
        </a>
    </div>
@endsection

@section('content')

    <div class="card mb-4">
        <div class="card-body">
            <form id="generate-form" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-6">
                    <label class="form-label font-bold" style="font-size:13px;">Choose a Lesson</label>
                    @if ($lessons->isEmpty())
                        <div class="alert alert-warning mb-0" style="font-size:13px;">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            No lessons are available yet. Ask your teacher to publish lesson content first.
                        </div>
                    @else
                        <select id="module-select" class="form-select" required>
                            <option value="" disabled selected>— Select a lesson —</option>
                            @foreach ($lessons as $lesson)
                                <option value="{{ $lesson->module_id }}">{{ $lesson->title }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="col-md-3">
                    <label class="form-label font-bold" style="font-size:13px;">How many?</label>
                    <input type="number" id="count-input" class="form-control" value="10" min="3" max="20">
                </div>
                <div class="col-md-3">
                    <button type="submit" id="generate-btn" class="btn btn-primary w-100 font-bold" {{ $lessons->isEmpty() ? 'disabled' : '' }}>
                        <i class="bi bi-stars"></i> Generate
                    </button>
                </div>
            </form>
            <p class="text-muted mt-2 mb-0" style="font-size:12px;">
                Flashcards are generated from the selected lesson's content.
            </p>
            <div id="generate-error" class="alert alert-danger mt-2 d-none"></div>
        </div>
    </div>

    <div class="row g-3" id="flashcard-grid">
        @forelse ($flashcards as $card)
            <div class="col-md-4 col-sm-6">
                <div class="flip-card" onclick="this.classList.toggle('flipped')">
                    <div class="flip-card-inner">
                        <div class="flip-card-front"><span>{{ $card->front_text }}</span></div>
                        <div class="flip-card-back"><span>{{ $card->back_text }}</span></div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted" id="empty-state">No flashcards yet — pick a lesson and generate some above to start reviewing.</p>
        @endforelse
    </div>

@endsection

@push('styles')
    <style>
        .flip-card { background-color: transparent; width: 100%; height: 180px; perspective: 1000px; cursor: pointer; }
        .flip-card-inner { position: relative; width: 100%; height: 100%; text-align: center; transition: transform 0.6s; transform-style: preserve-3d; }
        .flip-card.flipped .flip-card-inner { transform: rotateY(180deg); }
        .flip-card-front, .flip-card-back {
            position: absolute; width: 100%; height: 100%;
            -webkit-backface-visibility: hidden; backface-visibility: hidden;
            border-radius: .5rem; display: flex; align-items: center; justify-content: center;
            padding: 1rem; font-size: 14px; overflow-y: auto;
        }
        .flip-card-front { background-color: #fff; border: 1px solid #dee2e6; }
        .flip-card-back { background-color: #435ebe; color: #fff; transform: rotateY(180deg); }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            const form        = document.getElementById('generate-form');
            const moduleSelect = document.getElementById('module-select');
            const countInput  = document.getElementById('count-input');
            const btn         = document.getElementById('generate-btn');
            const grid        = document.getElementById('flashcard-grid');
            const errorBox    = document.getElementById('generate-error');
            const csrfToken   = document.querySelector('meta[name="csrf-token"]').content;
            const generateUrl = '{{ route('student.classes.courses.flashcards.generate', [$class->class_id, $course->course_id]) }}';

            function addCard(front, back) {
                const emptyState = document.getElementById('empty-state');
                if (emptyState) emptyState.remove();

                const col = document.createElement('div');
                col.className = 'col-md-4 col-sm-6';
                col.innerHTML = `
                <div class="flip-card" onclick="this.classList.toggle('flipped')">
                    <div class="flip-card-inner">
                        <div class="flip-card-front"><span></span></div>
                        <div class="flip-card-back"><span></span></div>
                    </div>
                </div>`;
                col.querySelector('.flip-card-front span').textContent = front;
                col.querySelector('.flip-card-back span').textContent = back;
                grid.appendChild(col);
            }

            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    errorBox.classList.add('d-none');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generating...';

                    fetch(generateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            module_id: moduleSelect ? moduleSelect.value : null,
                            count: countInput.value,
                        }),
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                data.flashcards.forEach(card => addCard(card.front_text, card.back_text));
                            } else {
                                errorBox.textContent = data.message || 'Something went wrong. Please try again.';
                                errorBox.classList.remove('d-none');
                            }
                        })
                        .catch(() => {
                            errorBox.textContent = 'Connection error. Please try again.';
                            errorBox.classList.remove('d-none');
                        })
                        .finally(() => {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-stars"></i> Generate';
                        });
                });
            }
        })();
    </script>
@endpush
