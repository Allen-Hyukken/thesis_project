@extends('layouts.app')

@section('title', $course->title . ' — EDITH Tutor')

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
@endsection

@section('page-heading')
    <div>
        <h3 class="mb-1">EDITH — AI Study Assistant</h3>
        <a href="{{ route('student.classes.courses.show', [$class->class_id, $course->course_id]) }}"
           class="text-muted" style="font-size:13px;">
            <i class="bi bi-arrow-left"></i> Back to {{ $course->title }}
        </a>
    </div>
@endsection

@section('content')

    <style>
        /* ── Site palette ───────────────────────────────────────
           Primary blue : #435ebe
           Dark navy    : #25396f
           Light blue bg: #ebf3ff
           Success green: #4fbe87
           Body text    : #555252
           Border grey  : #dfe3e7
        ─────────────────────────────────────────────────────── */

        .edith-chat-card {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(67,94,190,.15);
            max-width: 960px;
            margin: 0 auto;
        }

        /* ── Header ─────────────────────────────────────────── */
        .edith-chat-header {
            background: linear-gradient(135deg, #25396f 0%, #435ebe 100%);
            padding: 16px 22px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .edith-avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }
        .edith-avatar-wrap img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            object-position: top center;
            border: 2.5px solid rgba(255,255,255,.55);
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
        }
        .edith-status-dot {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            background: #4fbe87;
            border-radius: 50%;
            border: 2px solid #25396f;
        }
        .edith-chat-header .edith-name {
            flex-grow: 1;
        }
        .edith-chat-header .edith-name h6 {
            color: #ffffff;
            font-weight: 800;
            margin: 0 0 2px;
            font-size: 16px;
            letter-spacing: .4px;
            text-shadow: 0 1px 3px rgba(0,0,0,.3);
        }
        .edith-chat-header .edith-name span {
            color: rgba(255,255,255,.75);
            font-size: 12px;
        }
        .edith-status-text {
            color: #4fbe87;
            font-weight: 600;
        }
        .edith-course-badge {
            background: rgba(255,255,255,.15);
            color: #fff;
            font-size: 11.5px;
            padding: 4px 12px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,.3);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 220px;
            backdrop-filter: blur(4px);
        }

        /* ── Chat body ───────────────────────────────────────── */
        .edith-chat-body {
            background: #f5f7fb;
            height: 620px;
            overflow-y: auto;
            padding: 24px 22px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .edith-chat-body::-webkit-scrollbar { width: 5px; }
        .edith-chat-body::-webkit-scrollbar-thumb { background: #c5cfe8; border-radius: 10px; }

        /* ── Message rows ────────────────────────────────────── */
        .chat-row {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            margin-bottom: 10px;
        }
        .chat-row.user-row { flex-direction: row-reverse; }
        .chat-row > div:last-child        { display: flex; flex-direction: column; align-items: flex-start; }
        .chat-row.user-row > div:last-child { display: flex; flex-direction: column; align-items: flex-end; }

        /* EDITH mini avatar */
        .chat-row .mini-avatar img {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            object-position: top center;
            border: 1.5px solid #c5cfe8;
            flex-shrink: 0;
        }
        .chat-row.user-row .mini-avatar { display: none; }

        /* Bubbles */
        .chat-bubble {
            max-width: 72%;
            min-width: 60px;
            width: fit-content;
            padding: 11px 16px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.7;
            white-space: pre-wrap;
            word-break: break-word;
            overflow-wrap: anywhere;
            display: block;
            writing-mode: horizontal-tb;
            direction: ltr;
            text-align: left;
        }
        /* EDITH bubble */
        .chat-row:not(.user-row) .chat-bubble {
            background: #ffffff;
            border: 1px solid #dfe3e7;
            border-bottom-left-radius: 4px;
            color: #555252;
            box-shadow: 0 1px 4px rgba(67,94,190,.08);
        }
        /* User bubble */
        .chat-row.user-row .chat-bubble {
            background: linear-gradient(135deg, #435ebe, #25396f);
            color: #ffffff;
            border-bottom-right-radius: 4px;
            box-shadow: 0 2px 8px rgba(67,94,190,.3);
        }

        /* Timestamps */
        .chat-time {
            font-size: 10.5px;
            color: #adb5bd;
            margin-top: 3px;
            display: block;
        }
        .chat-row.user-row .chat-time { text-align: right; }

        /* Typing bubble */
        #typing-indicator .chat-bubble {
            background: #ffffff;
            border: 1px solid #dfe3e7;
            border-bottom-left-radius: 4px;
            color: #adb5bd;
            font-size: 13px;
        }
        .dot-typing {
            display: inline-flex;
            gap: 4px;
            align-items: center;
        }
        .dot-typing span {
            width: 7px; height: 7px;
            background: #435ebe;
            border-radius: 50%;
            animation: blink 1.2s infinite;
            opacity: .5;
        }
        .dot-typing span:nth-child(2) { animation-delay: .2s; }
        .dot-typing span:nth-child(3) { animation-delay: .4s; }
        @keyframes blink {
            0%, 80%, 100% { transform: scale(1); opacity: .4; }
            40%            { transform: scale(1.3); opacity: 1; }
        }

        /* Empty state */
        .edith-empty {
            text-align: center;
            margin: auto;
            color: #adb5bd;
            padding: 40px 0;
        }
        .edith-empty img {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            object-position: top center;
            opacity: .8;
            margin-bottom: 14px;
            border: 2px solid #c5cfe8;
        }
        .edith-empty .edith-empty-name {
            color: #435ebe;
            font-weight: 700;
            font-size: 15px;
        }

        /* ── Footer / input ──────────────────────────────────── */
        .edith-chat-footer {
            background: #ffffff;
            border-top: 1px solid #dfe3e7;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .edith-chat-footer .form-control {
            border-radius: 25px;
            border: 1.5px solid #c5cfe8;
            font-size: 14px;
            padding: 9px 18px;
            background: #f5f7fb;
            color: #555252;
            transition: border-color .2s, box-shadow .2s;
        }
        .edith-chat-footer .form-control:focus {
            border-color: #435ebe;
            box-shadow: 0 0 0 3px rgba(67,94,190,.12);
            background: #fff;
        }
        .edith-send-btn {
            width: 42px; height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #435ebe, #25396f);
            border: none;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform .15s, box-shadow .15s;
            box-shadow: 0 2px 8px rgba(67,94,190,.3);
        }
        .edith-send-btn:hover:not(:disabled) {
            transform: scale(1.08);
            box-shadow: 0 4px 14px rgba(67,94,190,.45);
        }
        .edith-send-btn:disabled { opacity: .5; cursor: not-allowed; }

        /* hint text */
        .edith-hint {
            font-size: 11px;
            color: #adb5bd;
            text-align: center;
            padding: 6px 0 2px;
            background: #fff;
        }
    </style>

    <p class="text-muted mb-3" style="font-size:13px; max-width:820px; margin:0 auto 12px;">
        Ask anything about <strong>{{ $course->title }}</strong>. EDITH's answers are based only on the lessons your teacher has published.
    </p>

    <div class="edith-chat-card card border-0">

        {{-- Header --}}
        <div class="edith-chat-header">
            <div class="edith-avatar-wrap">
                <img src="{{ asset('assets/images/edith-avatar.jpg') }}" alt="EDITH">
                <span class="edith-status-dot"></span>
            </div>
            <div class="edith-name">
                <h6>EDITH</h6>
                <span>AI Study Assistant &bull; Online</span>
            </div>
            <div class="edith-course-badge" title="{{ $course->title }}">
                <i class="bi bi-book me-1"></i>{{ $course->title }}
            </div>
        </div>

        {{-- Chat body --}}
        <div class="edith-chat-body" id="chat-window">

            @if ($history->isEmpty())
                <div class="edith-empty" id="empty-state">
                    <img src="{{ asset('assets/images/edith-avatar.jpg') }}" alt="EDITH">
                    <div class="edith-empty-name mb-1">Hi! I'm EDITH 👋</div>
                    <div style="font-size:13px;">Ask me anything about <strong>{{ $course->title }}</strong>.<br>I'm here to help you study!</div>
                </div>
            @else
                @foreach ($history as $turn)
                    <div class="chat-row {{ $turn->role === 'user' ? 'user-row' : '' }}">
                        @if ($turn->role !== 'user')
                            <div class="mini-avatar">
                                <img src="{{ asset('assets/images/edith-avatar.jpg') }}" alt="EDITH">
                            </div>
                        @endif
                        <div>
                            <div class="chat-bubble">{{ $turn->message }}</div>
                            <span class="chat-time">{{ $turn->created_at->format('g:i A') }}</span>
                        </div>
                    </div>
                @endforeach
            @endif

        </div>

        {{-- Footer --}}
        <div class="edith-chat-footer">
            <input type="text" id="question-input" class="form-control"
                   placeholder="Ask EDITH about this course..." maxlength="1000" autocomplete="off">
            <button class="edith-send-btn" id="ask-btn" title="Send">
                <i class="bi bi-send-fill" style="font-size:15px;"></i>
            </button>
        </div>
        <div class="edith-hint">Answers are based only on course lessons &bull; Press Enter to send</div>

    </div>

@endsection

@push('scripts')
    <script>
        (function () {
            const chatWindow  = document.getElementById('chat-window');
            const input       = document.getElementById('question-input');
            const btn         = document.getElementById('ask-btn');
            const csrfToken   = document.querySelector('meta[name="csrf-token"]').content;
            const askUrl      = '{{ route('student.classes.courses.ai-tutor.ask', [$class->class_id, $course->course_id]) }}';
            const edithAvatar = '{{ asset('assets/images/edith-avatar.jpg') }}';

            function now() {
                return new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
            }

            function appendBubble(message, role) {
                const emptyState = document.getElementById('empty-state');
                if (emptyState) emptyState.remove();

                const row = document.createElement('div');
                row.className = 'chat-row' + (role === 'user' ? ' user-row' : '');

                if (role !== 'user') {
                    row.innerHTML = `
                <div class="mini-avatar">
                    <img src="${edithAvatar}" alt="EDITH">
                </div>`;
                }

                const inner = document.createElement('div');
                inner.innerHTML = `
            <div class="chat-bubble">${escapeHtml(message)}</div>
            <span class="chat-time">${now()}</span>`;

                row.appendChild(inner);
                chatWindow.appendChild(row);
                chatWindow.scrollTop = chatWindow.scrollHeight;
            }

            function appendTyping() {
                const row = document.createElement('div');
                row.id = 'typing-indicator';
                row.className = 'chat-row';
                row.innerHTML = `
            <div class="mini-avatar">
                <img src="${edithAvatar}" alt="EDITH">
            </div>
            <div>
                <div class="chat-bubble">
                    <span class="dot-typing">
                        <span></span><span></span><span></span>
                    </span>
                </div>
            </div>`;
                chatWindow.appendChild(row);
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
                    .replace(/"/g, '&quot;')
                    .replace(/\n/g, '<br>');
            }

            function sendMessage() {
                const question = input.value.trim();
                if (!question) return;

                appendBubble(question, 'user');
                input.value = '';
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
                        appendBubble(data.success ? data.answer : (data.message || 'Something went wrong.'), 'assistant');
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
            input.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } });

            // scroll to bottom on load
            chatWindow.scrollTop = chatWindow.scrollHeight;
        })();
    </script>
@endpush
