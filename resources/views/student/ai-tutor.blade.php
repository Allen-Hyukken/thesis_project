@extends('layouts.app')

@section('title', $course->title . ' — AI Tutor')

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
@endsection

@section('page-heading')
    <div>
        <h3 class="mb-1"><i class="bi bi-robot me-1"></i> AI Study Assistant</h3>
        <a href="{{ route('student.classes.courses.show', [$class->class_id, $course->course_id]) }}" class="text-muted" style="font-size:13px;">
            <i class="bi bi-arrow-left"></i> Back to {{ $course->title }}
        </a>
    </div>
@endsection

@section('content')

    <p class="text-muted" style="font-size:13px;">
        Ask anything about <strong>{{ $course->title }}</strong>. Answers are based only on the lessons your teacher has published in this course.
    </p>

    <div class="card">
        <div class="card-body p-0">
            <div id="chat-window" class="p-3" style="height:480px; overflow-y:auto;">
                @forelse ($history as $turn)
                    <div class="d-flex mb-3 {{ $turn->role === 'user' ? 'justify-content-end' : 'justify-content-start' }}">
                        <div class="px-3 py-2 rounded-3 {{ $turn->role === 'user' ? 'bg-primary text-white' : 'bg-light border' }}" style="max-width:75%; white-space:pre-wrap; font-size:14px;">
                            {{ $turn->message }}
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center mt-5" id="empty-state">
                        <i class="bi bi-chat-dots fs-2 d-block mb-2"></i>
                        Ask your first question about this course.
                    </p>
                @endforelse
            </div>

            <form id="ask-form" class="border-top d-flex p-2 gap-2">
                @csrf
                <input type="text" id="question-input" class="form-control" placeholder="Ask a question about this course..." maxlength="1000" required autocomplete="off">
                <button type="submit" id="ask-btn" class="btn btn-primary font-bold">
                    <i class="bi bi-send"></i> Send
                </button>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        (function () {
            const chatWindow = document.getElementById('chat-window');
            const form = document.getElementById('ask-form');
            const input = document.getElementById('question-input');
            const btn = document.getElementById('ask-btn');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const askUrl = '{{ route('student.classes.courses.ai-tutor.ask', [$class->class_id, $course->course_id]) }}';

            function appendBubble(message, role) {
                const emptyState = document.getElementById('empty-state');
                if (emptyState) emptyState.remove();

                const wrap = document.createElement('div');
                wrap.className = 'd-flex mb-3 ' + (role === 'user' ? 'justify-content-end' : 'justify-content-start');

                const bubble = document.createElement('div');
                bubble.className = 'px-3 py-2 rounded-3 ' + (role === 'user' ? 'bg-primary text-white' : 'bg-light border');
                bubble.style.maxWidth = '75%';
                bubble.style.whiteSpace = 'pre-wrap';
                bubble.style.fontSize = '14px';
                bubble.textContent = message;

                wrap.appendChild(bubble);
                chatWindow.appendChild(wrap);
                chatWindow.scrollTop = chatWindow.scrollHeight;
            }

            function appendTyping() {
                const wrap = document.createElement('div');
                wrap.id = 'typing-indicator';
                wrap.className = 'd-flex mb-3 justify-content-start';
                wrap.innerHTML = '<div class="px-3 py-2 rounded-3 bg-light border text-muted" style="font-size:14px;"><i class="bi bi-three-dots"></i> Thinking...</div>';
                chatWindow.appendChild(wrap);
                chatWindow.scrollTop = chatWindow.scrollHeight;
            }

            function removeTyping() {
                const el = document.getElementById('typing-indicator');
                if (el) el.remove();
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const question = input.value.trim();
                if (! question) return;

                appendBubble(question, 'user');
                input.value = '';
                input.disabled = true;
                btn.disabled = true;
                appendTyping();

                fetch(askUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ question: question }),
                })
                    .then(res => res.json())
                    .then(data => {
                        removeTyping();
                        if (data.success) {
                            appendBubble(data.answer, 'assistant');
                        } else {
                            appendBubble(data.message || 'Something went wrong. Please try again.', 'assistant');
                        }
                    })
                    .catch(() => {
                        removeTyping();
                        appendBubble('Connection error. Please try again.', 'assistant');
                    })
                    .finally(() => {
                        input.disabled = false;
                        btn.disabled = false;
                        input.focus();
                    });
            });
        })();
    </script>
@endpush
