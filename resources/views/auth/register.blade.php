<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        (function () {
            var t = localStorage.getItem('tup-theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <title>Sign up — TUP-LMS</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pages/auth-modern.css') }}">
</head>
<body>

<div class="auth2-shell">

    <div class="auth2-left">
        <div class="auth2-top">
            <a href="{{ url('/') }}" class="auth2-brand">
            </a>
            <div class="auth2-toggle-group">
                <i class="bi bi-moon-stars-fill" id="theme-toggle-icon"></i>
                <label class="theme-switch">
                    <input type="checkbox" id="theme-toggle-checkbox">
                    <span class="theme-switch-slider"></span>
                </label>
            </div>
        </div>

        <div class="auth2-form-wrap">
            <div class="auth2-eyebrow">Get started</div>
            <h1 class="auth2-h1 compact">Create your account</h1>
            <p class="auth2-sub">Your course. Your AI. Your way.</p>

            @if ($errors->any())
                <div class="auth2-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST">
                @csrf

                <div class="auth2-field">
                    <label class="auth2-label" for="name">Username</label>
                    <div class="auth2-input-wrap">
                        <i class="bi bi-person"></i>
                        <input type="text" name="name" id="name"
                               class="auth2-input @error('name') is-invalid @enderror"
                               placeholder="Your username" value="{{ old('name') }}">
                    </div>
                </div>

                <div class="auth2-field">
                    <label class="auth2-label" for="email">Email</label>
                    <div class="auth2-input-wrap">
                        <i class="bi bi-envelope"></i>
                        <input type="text" name="email" id="email"
                               class="auth2-input @error('email') is-invalid @enderror"
                               placeholder="you@example.com" value="{{ old('email') }}">
                    </div>
                </div>

                <div class="auth2-field">
                    <label class="auth2-label" for="password">Password</label>
                    <div class="auth2-input-wrap">
                        <i class="bi bi-shield-lock"></i>
                        <input type="password" name="password" id="password"
                               class="auth2-input @error('password') is-invalid @enderror"
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="auth2-field">
                    <label class="auth2-label" for="password_confirmation">Confirm password</label>
                    <div class="auth2-input-wrap">
                        <i class="bi bi-shield-lock"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="auth2-input" placeholder="••••••••">
                    </div>
                </div>

                <label class="auth2-label">I am a:</label>
                <div class="auth2-role-group">
                    <label class="auth2-role">
                        <input type="radio" name="role" value="student" {{ old('role', 'student') === 'student' ? 'checked' : '' }}>
                        <i class="bi bi-mortarboard"></i>
                        <span>Student</span>
                    </label>
                    <label class="auth2-role">
                        <input type="radio" name="role" value="teacher" {{ old('role') === 'teacher' ? 'checked' : '' }}>
                        <i class="bi bi-person-video3"></i>
                        <span>Teacher</span>
                    </label>
                </div>

                <button type="submit" class="auth2-btn">
                    Sign up <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="auth2-foot">
                <p>Already have an account? <a href="{{ route('login') }}">Log in</a></p>
            </div>
        </div>
    </div>

    <div class="auth2-right">
        <span class="auth2-orb auth2-orb-1"></span>
        <span class="auth2-orb auth2-orb-2"></span>

        <div class="auth2-card">
            <div class="auth2-card-head">
                <div class="auth2-card-avatar"><i class="bi bi-layers-fill"></i></div>
                <div>
                    <div class="auth2-card-name">AI Flashcards</div>
                    <div class="auth2-card-tag">Generated in seconds</div>
                </div>
            </div>
            <div class="auth2-bubble">Big-O Notation</div>
            <div class="auth2-bubble alt">O(n) — time grows linearly with input size.</div>
        </div>

        <div class="auth2-tagline">
            <h2>Join a smarter classroom.</h2>
            <p>Whether you're teaching or learning, TUP-LMS adapts the tools to fit you.</p>
        </div>
    </div>

</div>

<script>
    (function () {
        const checkbox = document.getElementById('theme-toggle-checkbox');
        const icon = document.getElementById('theme-toggle-icon');

        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            if (checkbox) checkbox.checked = theme === 'dark';
            if (icon) {
                icon.classList.toggle('bi-moon-stars-fill', theme !== 'dark');
                icon.classList.toggle('bi-sun-fill', theme === 'dark');
            }
        }

        applyTheme(localStorage.getItem('tup-theme') || 'light');

        if (checkbox) {
            checkbox.addEventListener('change', function () {
                const theme = checkbox.checked ? 'dark' : 'light';
                localStorage.setItem('tup-theme', theme);
                applyTheme(theme);
            });
        }
    })();
</script>
</body>
</html>
