@extends('layouts.app')

@section('title', $course->title . ' — EDITH Tutor')

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
@endsection

{{-- No page-heading — EDITH fills the full dashboard area --}}
@section('page-heading')@endsection

@section('content')

    @push('styles')
        <style>
            /* ── Pull out of the page-content padding so EDITH fills the full area ── */
            .page-content { padding: 0 !important; }
            .page-heading  { margin: 0 !important; }

            /* ── EDITH full-screen shell ─────────────────────────────────────── */
            #edith-shell {
                display: flex;
                flex-direction: column;
                height: calc(100vh - 72px); /* 72px = header burger bar */
                background: #f5f7fb;
                overflow: hidden;
            }

            /* ── Top bar ─────────────────────────────────────────────────────── */
            .edith-topbar {
                display: flex;
                align-items: center;
                gap: 14px;
                padding: 12px 24px;
                background: linear-gradient(135deg, #25396f 0%, #435ebe 100%);
                flex-shrink: 0;
                box-shadow: 0 2px 10px rgba(37,57,111,.25);
            }
            .edith-topbar .avatar-wrap { position: relative; flex-shrink: 0; }
            .edith-topbar .avatar-wrap img {
                width: 44px; height: 44px;
                border-radius: 50%;
                object-fit: cover; object-position: top center;
                border: 2px solid rgba(255,255,255,.45);
                box-shadow: 0 2px 8px rgba(0,0,0,.25);
            }
            .edith-topbar .status-dot {
                position: absolute; bottom: 1px; right: 1px;
                width: 11px; height: 11px;
                background: #4fbe87; border-radius: 50%;
                border: 2px solid #25396f;
            }
            .edith-topbar .edith-info { flex-grow: 1; }
            .edith-topbar .edith-info h6 {
                color: #fff; font-weight: 800; margin: 0 0 1px;
                font-size: 15px; letter-spacing: .3px;
                text-shadow: 0 1px 3px rgba(0,0,0,.25);
            }
            .edith-topbar .edith-info span {
                color: rgba(255,255,255,.72); font-size: 11.5px;
            }
            .edith-topbar .edith-info .online { color: #4fbe87; font-weight: 600; }
            .edith-course-pill {
                background: rgba(255,255,255,.14);
                color: #fff;
                font-size: 11.5px;
                padding: 5px 14px;
                border-radius: 20px;
                border: 1px solid rgba(255,255,255,.28);
                backdrop-filter: blur(4px);
                white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
                max-width: 240px;
            }
            .edith-back-link {
                color: rgba(255,255,255,.7);
                font-size: 12px;
                text-decoration: none;
                display: flex; align-items: center; gap: 5px;
                padding: 5px 12px;
                border-radius: 20px;
                border: 1px solid rgba(255,255,255,.25);
                transition: background .2s;
                flex-shrink: 0;
                white-space: nowrap;
            }
            .edith-back-link:hover { background: rgba(255,255,255,.12); color: #fff; }

            /* ── Chat messages area ───────────────────────────────────────────── */
            #chat-window {
                flex: 1;
                overflow-y: auto;
                padding: 28px 0;
                display: flex;
                flex-direction: column;
                gap: 2px;
            }
            #chat-window::-webkit-scrollbar { width: 5px; }
            #chat-window::-webkit-scrollbar-thumb { background: #c5cfe8; border-radius: 10px; }

            /* Width-constrained inner wrapper for messages */
            .chat-inner {
                width: 100%;
                max-width: 820px;
                margin: 0 auto;
                padding: 0 24px;
            }

            /* ── Message rows ──────────────────────────────────────────────────── */
            .chat-row {
                display: flex;
                align-items: flex-end;
                gap: 10px;
                margin-bottom: 16px;
            }
            .chat-row.user-row { flex-direction: row-reverse; }

            .mini-avatar img {
                width: 32px; height: 32px;
                border-radius: 50%;
                object-fit: cover; object-position: top center;
                border: 1.5px solid #c5cfe8;
                flex-shrink: 0;
            }
            .chat-row.user-row .mini-avatar { display: none; }

            .chat-col { display: flex; flex-direction: column; align-items: flex-start; min-width: 0; }
            .chat-row.user-row .chat-col { align-items: flex-end; }

            /* ── Bubbles ─────────────────────────────────────────────────────── */
            .chat-bubble {
                max-width: 680px;
                min-width: 48px;
                padding: 12px 16px;
                border-radius: 18px;
                font-size: 14px;
                line-height: 1.7;
                word-break: break-word;
                overflow-wrap: anywhere;
            }

            /* EDITH bubble — white card, markdown inside */
            .chat-row:not(.user-row) .chat-bubble {
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-bottom-left-radius: 4px;
                color: #1a1a1a;
                box-shadow: 0 1px 4px rgba(67,94,190,.08);
            }

            /* User bubble — gradient */
            .chat-row.user-row .chat-bubble {
                background: linear-gradient(135deg, #435ebe, #25396f);
                color: #fff;
                border-bottom-right-radius: 4px;
                box-shadow: 0 2px 8px rgba(67,94,190,.28);
                white-space: pre-wrap;
            }

            .chat-time {
                font-size: 10.5px; color: #adb5bd;
                margin-top: 4px; display: block;
            }
            .chat-row.user-row .chat-time { text-align: right; }

            /* ── Markdown inside EDITH bubble (same tokens as lesson content) ── */
            .edith-md { }
            .edith-md h2 {
                font-size: 14.5px; font-weight: 700;
                margin: 1.2em 0 .45em; color: #111;
                padding-bottom: 3px; border-bottom: 1px solid #f0f0f0;
            }
            .edith-md h3 { font-size: 13.5px; font-weight: 700; margin: 1em 0 .35em; color: #222; }
            .edith-md p { margin: 0 0 .7em; }
            .edith-md p:last-child { margin-bottom: 0; }
            .edith-md ul, .edith-md ol { padding-left: 1.4em; margin: 0 0 .7em; }
            .edith-md li { margin-bottom: .25em; }
            .edith-md li > ul, .edith-md li > ol { margin-top: .2em; }
            .edith-md strong { font-weight: 700; color: #111; }
            .edith-md em { color: #555; }
            .edith-md code {
                background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 4px;
                padding: 1px 5px; font-size: 12.5px;
                font-family: 'SFMono-Regular', Consolas, monospace; color: #c7254e;
            }
            .edith-md pre {
                background: #f6f8fa; border: 1px solid #e5e7eb; border-radius: 8px;
                padding: 12px 14px; overflow-x: auto; margin-bottom: .7em;
            }
            .edith-md pre code { background: none; border: none; padding: 0; color: #24292e; }
            .edith-md blockquote {
                border-left: 3px solid #10a37f; background: #f0fdf8;
                margin: 0 0 .7em; padding: 9px 14px;
                border-radius: 0 6px 6px 0; color: #065f46; font-size: 13px;
            }
            .edith-md blockquote p { margin: 0; }
            .edith-md table { width: 100%; border-collapse: collapse; margin-bottom: .7em; font-size: 13px; }
            .edith-md th {
                background: #f9fafb; font-weight: 700; text-align: left;
                padding: 7px 11px; border: 1px solid #e5e7eb; color: #374151;
            }
            .edith-md td { padding: 6px 11px; border: 1px solid #e5e7eb; vertical-align: top; }
            .edith-md tr:nth-child(even) td { background: #f9fafb; }
            .edith-md hr { border: none; border-top: 1px solid #e5e7eb; margin: 1em 0; }
            .edith-md > h2:first-child { margin-top: 0; }

            /* ── Typing indicator ─────────────────────────────────────────────── */
            #typing-indicator .chat-bubble {
                background: #fff; border: 1px solid #e5e7eb;
                border-bottom-left-radius: 4px;
            }
            .dot-typing { display: inline-flex; gap: 5px; align-items: center; padding: 2px 0; }
            .dot-typing span {
                width: 7px; height: 7px; background: #435ebe;
                border-radius: 50%; opacity: .4;
                animation: edith-blink 1.2s infinite;
            }
            .dot-typing span:nth-child(2) { animation-delay: .2s; }
            .dot-typing span:nth-child(3) { animation-delay: .4s; }
            @keyframes edith-blink {
                0%, 80%, 100% { transform: scale(1); opacity: .4; }
                40%            { transform: scale(1.35); opacity: 1; }
            }

            /* ── Empty / welcome state ────────────────────────────────────────── */
            #empty-state {
                text-align: center; color: #adb5bd;
                padding: 60px 20px; margin: auto;
            }
            #empty-state img {
                width: 68px; height: 68px; border-radius: 50%;
                object-fit: cover; object-position: top center;
                opacity: .85; margin-bottom: 14px;
                border: 2px solid #c5cfe8;
            }
            #empty-state .empty-name { color: #435ebe; font-weight: 700; font-size: 16px; }
            #empty-state p { font-size: 13px; color: #9aa4b2; max-width: 320px; margin: 6px auto 0; }

            /* Suggested prompts */
            .prompt-chip {
                display: inline-block;
                background: #fff;
                border: 1px solid #c5cfe8;
                color: #435ebe;
                font-size: 12.5px;
                padding: 6px 14px;
                border-radius: 20px;
                cursor: pointer;
                margin: 4px;
                transition: background .15s, border-color .15s;
            }
            .prompt-chip:hover { background: #ebf3ff; border-color: #435ebe; }

            /* ── Input footer ──────────────────────────────────────────────────── */
            .edith-footer {
                flex-shrink: 0;
                background: #fff;
                border-top: 1px solid #e5e7eb;
                padding: 12px 24px 14px;
            }
            .edith-footer-inner {
                max-width: 820px;
                margin: 0 auto;
                display: flex;
                align-items: flex-end;
                gap: 10px;
            }
            .edith-footer textarea {
                flex: 1;
                border-radius: 14px;
                border: 1.5px solid #c5cfe8;
                font-size: 14px;
                padding: 10px 16px;
                background: #f5f7fb;
                color: #333;
                resize: none;
                min-height: 44px;
                max-height: 140px;
                line-height: 1.55;
                transition: border-color .2s, box-shadow .2s;
                outline: none;
                overflow-y: auto;
            }
            .edith-footer textarea:focus {
                border-color: #435ebe;
                box-shadow: 0 0 0 3px rgba(67,94,190,.1);
                background: #fff;
            }
            .edith-send-btn {
                width: 44px; height: 44px; flex-shrink: 0;
                border-radius: 50%;
                background: linear-gradient(135deg, #435ebe, #25396f);
                border: none; color: #fff;
                display: flex; align-items: center; justify-content: center;
                box-shadow: 0 2px 8px rgba(67,94,190,.3);
                transition: transform .15s, box-shadow .15s;
                cursor: pointer;
            }
            .edith-send-btn:hover:not(:disabled) {
                transform: scale(1.08);
                box-shadow: 0 4px 14px rgba(67,94,190,.45);
            }
            .edith-send-btn:disabled { opacity: .5; cursor: not-allowed; }
            .edith-hint {
                text-align: center; font-size: 11px; color: #c5cfe8;
                padding: 5px 0 0; max-width: 820px; margin: 0 auto;
            }
        </style>
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
