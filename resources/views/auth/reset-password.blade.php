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
            <h1 class="auth2-h1">Create new password</h1>
            <p class="auth2-sub">Please choose a secure password that you haven't used here before.</p>

            @if ($errors->any())
                <div class="auth2-error" style="color: #dc3545; margin-bottom: 1rem;">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('password.update') }}" method="POST">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="auth2-field">
                    <label class="auth2-label" for="password">New Password</label>
                    <div class="auth2-input-wrap">
                        <i class="bi bi-key"></i>
                        <input type="password" name="password" id="password"
                               class="auth2-input"
                               placeholder="Minimum 8 characters" required>
                    </div>
                </div>

                <div class="auth2-field" style="margin-top: 1rem;">
                    <label class="auth2-label" for="password_confirmation">Confirm Password</label>
                    <div class="auth2-input-wrap">
                        <i class="bi bi-key-fill"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="auth2-input"
                               placeholder="Repeat new password" required>
                    </div>
                </div>

                <button type="submit" class="auth2-btn" style="margin-top: 1.5rem;">
                    Update password <i class="bi bi-check-circle"></i>
                </button>
            </form>

            <div class="auth2-foot">
                <p>Nevermind, take me back to <a href="{{ route('login') }}">Log in</a></p>
            </div>
        </div>
    </div>

    <div class="auth2-right">
        <span class="auth2-orb auth2-orb-1"></span>
        <span class="auth2-orb auth2-orb-2"></span>

        <div class="auth2-card">
            <div class="auth2-card-head">
                <div class="auth2-card-avatar"><i class="bi bi-shield-check"></i></div>
                <div>
                    <div class="auth2-card-name">Strong Security</div>
                    <div class="auth2-card-tag">Encrypted session</div>
                </div>
            </div>
            <div class="auth2-bubble">Once updated, your token is destroyed immediately.</div>
            <div class="auth2-bubble alt">You will be redirected straight to log in.</div>
        </div>

        <div class="auth2-tagline">
            <h2>Secure your space.</h2>
            <p>One update, one confirmation, and you're safely back to your dashboard.</p>
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
