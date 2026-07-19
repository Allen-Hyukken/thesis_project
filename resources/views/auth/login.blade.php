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
    <title>Log in — TUP-LMS</title>
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
            <div class="auth2-eyebrow">Welcome back</div>
            <h1 class="auth2-h1">Log in to TUP-LMS</h1>
            <p class="auth2-sub">Your course. Your AI. Your way.</p>

            @if ($errors->any())
                <div class="auth2-error">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf

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

                <label class="auth2-remember">
                    <input type="checkbox" name="remember" id="flexCheckDefault">
                    Keep me logged in
                </label>

                <button type="submit" class="auth2-btn">
                    Log in <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="auth2-foot">
                <p>Don't have an account? <a href="{{ route('register') }}">Sign up</a></p>
                <p><a href="{{ route('password.request') }}">Forgot password?</a></p>
            </div>
        </div>
    </div>

    <div class="auth2-right">
        <span class="auth2-orb auth2-orb-1"></span>
        <span class="auth2-orb auth2-orb-2"></span>

        <div class="auth2-card">
            <div class="auth2-card-head">
                <div class="auth2-card-avatar"><i class="bi bi-stars"></i></div>
                <div>
                    <div class="auth2-card-name">AI Tutor</div>
                    <div class="auth2-card-tag">Online now</div>
                </div>
            </div>
            <div class="auth2-bubble">What's a Linked List? I keep mixing it up with arrays.</div>
            <div class="auth2-bubble alt">Think of it as a chain of boxes — each one points to the next!</div>
        </div>

        <div class="auth2-tagline">
            <h2>Learn at the speed of curiosity.</h2>
            <p>Flashcards, tutoring, and feedback that adapt to every student and every class.</p>
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
