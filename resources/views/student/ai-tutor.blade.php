@extends('layouts.app')

@section('title', $course->title . ' — EDITH Tutor')

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
@endsection

{{-- No page-heading — EDITH fills the full dashboard area --}}
@section('page-heading')@endsection

@section('content')

    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/pages/edith-tutor.css') }}">
    @endpush

    <div id="edith-shell">

        {{-- ── Top bar ──────────────────────────────────────────── --}}
        <div class="edith-topbar">
            <div class="avatar-wrap">
                <img src="{{ asset('assets/images/edith-avatar.jpg') }}" alt="EDITH">
                <span class="status-dot"></span>
            </div>
            <div class="edith-info">
                <h6>EDITH</h6>
                <span>AI Study Assistant &bull; <span class="online">Online</span></span>
            </div>
            <div class="edith-course-pill" title="{{ $course->title }}">
                <i class="bi bi-book me-1"></i>{{ $course->title }}
            </div>
            <a href="{{ route('student.classes.courses.show', [$class->class_id, $course->course_id]) }}"
               class="edith-back-link">
                <i class="bi bi-arrow-left"></i> Back to Course
            </a>
        </div>

        {{-- ── Messages ─────────────────────────────────────────── --}}
        <div id="chat-window">
            <div class="chat-inner">

                @if ($history->isEmpty())
                    <div id="empty-state">
                        <img src="{{ asset('assets/images/edith-avatar.jpg') }}" alt="EDITH">
                        <div class="empty-name mb-1">Hi! I'm EDITH 👋</div>
                        <p>I'm your AI study assistant for <strong>{{ $course->title }}</strong>.<br>Ask me anything covered in your lessons!</p>
                        <div class="mt-3">
                            <span class="prompt-chip" onclick="usePrompt(this)">Explain the main concepts</span>
                            <span class="prompt-chip" onclick="usePrompt(this)">Give me a summary</span>
                            <span class="prompt-chip" onclick="usePrompt(this)">What should I study for the exam?</span>
                            <span class="prompt-chip" onclick="usePrompt(this)">Give me a sample question</span>
                        </div>
                    </div>
                @else
                    @foreach ($history as $turn)
                        <div class="chat-row {{ $turn->role === 'user' ? 'user-row' : '' }}">
                            @if ($turn->role !== 'user')
                                <div class="mini-avatar">
                                    <img src="{{ asset('assets/images/edith-avatar.jpg') }}" alt="EDITH">
                                </div>
                            @endif
                            <div class="chat-col">
                                @if ($turn->role === 'user')
                                    <div class="chat-bubble">{{ $turn->message }}</div>
                                @else
                                    <div class="chat-bubble">
                                        <div class="edith-md" data-raw="{{ e($turn->message) }}"></div>
                                    </div>
                                @endif
                                <span class="chat-time">{{ $turn->created_at->format('g:i A') }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif

            </div>
        </div>

        {{-- ── Input footer ─────────────────────────────────────── --}}
        <div class="edith-footer">
            <div class="edith-footer-inner">
            <textarea id="question-input"
                      placeholder="Ask EDITH about {{ $course->title }}…"
                      maxlength="1000"
                      rows="1"
                      autocomplete="off"></textarea>
                <button class="edith-send-btn" id="ask-btn" title="Send">
                    <i class="bi bi-send-fill" style="font-size:15px;"></i>
                </button>
            </div>
            <div class="edith-hint">Answers are based only on published course lessons &bull; Enter to send &bull; Shift+Enter for new line</div>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/9.1.6/marked.min.js"
            onload="marked.setOptions({breaks:true,gfm:true})"></script>
    <script>
        (function () {
            const chatWindow  = document.getElementById('chat-window');
            const input       = document.getElementById('question-input');
            const btn         = document.getElementById('ask-btn');
            const csrfToken   = document.querySelector('meta[name="csrf-token"]').content;
            const askUrl      = '{{ route('student.classes.courses.ai-tutor.ask', [$class->class_id, $course->course_id]) }}';
            const edithAvatar = '{{ asset('assets/images/edith-avatar.jpg') }}';

            // ── Render existing history markdown ──────────────────────────
            document.querySelectorAll('.edith-md[data-raw]').forEach(el => {
                const raw = el.getAttribute('data-raw');
                if (raw && raw.trim() && window.marked) {
                    el.innerHTML = marked.parse(raw);
                }
            });

            // ── Auto-grow textarea ────────────────────────────────────────
            input.addEventListener('input', function () {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 140) + 'px';
            });

            // ── Suggested prompt chips ────────────────────────────────────
            window.usePrompt = function (el) {
                input.value = el.textContent.trim();
                input.focus();
                input.dispatchEvent(new Event('input'));
            };

            function now() {
                return new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
            }

            function getInner() {
                let inner = chatWindow.querySelector('.chat-inner');
                if (!inner) {
                    inner = document.createElement('div');
                    inner.className = 'chat-inner';
                    chatWindow.appendChild(inner);
                }
                return inner;
            }

            function appendBubble(message, role) {
                const emptyState = document.getElementById('empty-state');
                if (emptyState) emptyState.remove();

                const inner = getInner();
                const row   = document.createElement('div');
                row.className = 'chat-row' + (role === 'user' ? ' user-row' : '');

                if (role !== 'user') {
                    const av = document.createElement('div');
                    av.className = 'mini-avatar';
                    av.innerHTML = `<img src="${edithAvatar}" alt="EDITH">`;
                    row.appendChild(av);
                }

                const col = document.createElement('div');
                col.className = 'chat-col';

                if (role === 'user') {
                    col.innerHTML = `
                <div class="chat-bubble">${escapeHtml(message)}</div>
                <span class="chat-time">${now()}</span>`;
                } else {
                    const bubble = document.createElement('div');
                    bubble.className = 'chat-bubble';
                    const md = document.createElement('div');
                    md.className = 'edith-md';
                    md.innerHTML = window.marked ? marked.parse(message) : escapeHtml(message);
                    bubble.appendChild(md);
                    const time = document.createElement('span');
                    time.className = 'chat-time';
                    time.textContent = now();
                    col.appendChild(bubble);
                    col.appendChild(time);
                    row.appendChild(col);
                    inner.appendChild(row);
                    chatWindow.scrollTop = chatWindow.scrollHeight;
                    return;
                }

                row.appendChild(col);
                inner.appendChild(row);
                chatWindow.scrollTop = chatWindow.scrollHeight;
            }

            function appendTyping() {
                const inner = getInner();
                const row = document.createElement('div');
                row.id = 'typing-indicator';
                row.className = 'chat-row';
                row.innerHTML = `
            <div class="mini-avatar"><img src="${edithAvatar}" alt="EDITH"></div>
            <div class="chat-col">
                <div class="chat-bubble">
                    <span class="dot-typing"><span></span><span></span><span></span></span>
                </div>
            </div>`;
                inner.appendChild(row);
                chatWindow.scrollTop = chatWindow.scrollHeight;
            }

            function removeTyping() {
                const el = document.getElementById('typing-indicator');
                if (el) el.remove();
            }

            function escapeHtml(text) {
                return text
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function sendMessage() {
                const question = input.value.trim();
                if (!question || btn.disabled) return;

                appendBubble(question, 'user');
                input.value = '';
                input.style.height = 'auto';
                input.disabled = true;
                btn.disabled   = true;
                appendTyping();

                fetch(askUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ question }),
                })
                    .then(r => r.json())
                    .then(data => {
                        removeTyping();
                        appendBubble(
                            data.success ? data.answer : (data.message || 'Something went wrong.'),
                            'assistant'
                        );
                    })
                    .catch(() => {
                        removeTyping();
                        appendBubble('Connection error. Please try again.', 'assistant');
                    })
                    .finally(() => {
                        input.disabled = false;
                        btn.disabled   = false;
                        input.focus();
                    });
            }

            btn.addEventListener('click', sendMessage);
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            // Scroll to bottom on load
            chatWindow.scrollTop = chatWindow.scrollHeight;
        })();
    </script>
@endpush
