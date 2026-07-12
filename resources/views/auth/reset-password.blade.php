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
    <title>Reset password — Acadly</title>
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
            <a href="{{ url('/') }}" class="auth2-brand"></a>
            <div class="auth2-toggle-group">
                <i class="bi bi-moon-stars-fill" id="theme-toggle-icon"></i>
                <label class="theme-switch">
                    <input type="checkbox" id="theme-toggle-checkbox">
                    <span class="theme-switch-slider"></span>
                </label>
            </div>
        </div>

        <div class="auth2-form-wrap">
            <div class="auth2-eyebrow">Account recovery</div>
            <h1 class="auth2-h1">Set a new password</h1>
            <p class="auth2-sub">Choose a strong new password for your account.</p>

            @if ($errors->any())
                <div class="auth2-error">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="auth2-field">
                    <label class="auth2-label" for="email">Email</label>
                    <div class="auth2-input-wrap">
                        <i class="bi bi-envelope"></i>
                        <input type="email" name="email" id="email"
                               class="auth2-input @error('email') is-invalid @enderror"
                               placeholder="you@example.com" value="{{ old('email', $email) }}" required>
                    </div>
                </div>

                <div class="auth2-field">
                    <label class="auth2-label" for="password">New password</label>
                    <div class="auth2-input-wrap">
                        <i class="bi bi-lock"></i>
                        <input type="password" name="password" id="password"
                               class="auth2-input @error('password') is-invalid @enderror"
                               placeholder="At least 8 characters" required minlength="8">
                    </div>
                </div>

                <div class="auth2-field">
                    <label class="auth2-label" for="password_confirmation">Confirm new password</label>
                    <div class="auth2-input-wrap">
                        <i class="bi bi-lock-fill"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="auth2-input"
                               placeholder="Re-enter your new password" required minlength="8">
                    </div>
                </div>

                <button type="submit" class="auth2-btn">
                    Reset password <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="auth2-foot">
                <p>Remember your password? <a href="{{ route('login') }}">Log in</a></p>
            </div>
        </div>
    </div>

    <div class="auth2-right">
        <span class="auth2-orb auth2-orb-1"></span>
        <span class="auth2-orb auth2-orb-2"></span>

        <div class="auth2-card">
            <div class="auth2-card-head">
                <div class="auth2-card-avatar"><i class="bi bi-shield-lock"></i></div>
                <div>
                    <div class="auth2-card-name">Account security</div>
                    <div class="auth2-card-tag">Quick & secure</div>
                </div>
            </div>
            <div class="auth2-bubble">Almost there — set your new password.</div>
            <div class="auth2-bubble alt">This link expires in 60 minutes.</div>
        </div>

        <div class="auth2-tagline">
            <h2>Almost back in.</h2>
            <p>Pick a new password and you're good to go.</p>
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
