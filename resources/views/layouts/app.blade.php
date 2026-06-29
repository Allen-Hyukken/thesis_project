<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (function () {
            var t = localStorage.getItem('tup-theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <title>@yield('title', 'TUP LMS') — TUP AI Learning</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/vendors/perfect-scrollbar/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/iconly/bold.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">

    @stack('styles')
</head>

<body>
<div id="app">

    <div id="sidebar" class="active">
        <div class="sidebar-wrapper active">

            <div class="sidebar-header">
                <div class="d-flex justify-content-between">
                    <div class="logo">
                        <a href="#">
                            <h5 class="fw-bold mb-0">
                                <span style="color:#435ebe;">TUP</span><span style="color:#c0392b;">-LMS</span>
                            </h5>
                            <small class="text-muted" style="font-size:11px;font-weight:600;">AI Learning Platform</small>
                        </a>
                    </div>
                    <div class="toggler">
                        <a href="#" class="sidebar-hide d-xl-none d-block">
                            <i class="bi bi-x bi-middle"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="sidebar-menu">
                <ul class="menu">
                    @yield('sidebar-nav')
                    <li class="sidebar-item theme-toggle-item">
                        <a href="#" id="theme-toggle-btn" class="sidebar-link">
                            <i class="bi bi-moon-stars-fill" id="theme-toggle-icon"></i>
                            <span>Dark Mode</span>
                            <label class="theme-switch ms-auto">
                                <input type="checkbox" id="theme-toggle-checkbox">
                                <span class="theme-switch-slider"></span>
                            </label>
                        </a>
                    </li>
                </ul>
            </div>

            <button class="sidebar-toggler btn x">
                <i data-feather="x"></i>
            </button>

        </div>
    </div>

    <div id="main">

        <header class="mb-3">
            <a href="#" class="burger-btn d-block">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>

        <div class="page-heading">
            @yield('page-heading')
        </div>

        <div class="page-content">
            @yield('content')
        </div>

        <footer>
            <div class="footer clearfix mb-0 text-muted">
                <div class="float-start">
                    <p>{{ date('Y') }} &copy; TUP-LMS</p>
                </div>
                <div class="float-end">
                    <p>Crafted with <span class="text-danger"><i class="bi bi-heart"></i></span> by TUP</p>
                </div>
            </div>
        </footer>

    </div>

</div>

<script src="{{ asset('assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>
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

        document.getElementById('theme-toggle-btn').addEventListener('click', function (e) {
            e.preventDefault();
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change'));
        });

        if (checkbox) {
            checkbox.addEventListener('change', function () {
                const theme = checkbox.checked ? 'dark' : 'light';
                localStorage.setItem('tup-theme', theme);
                applyTheme(theme);
            });
        }
    })();
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.5/sweetalert2.all.min.js"></script>

@stack('scripts')
</body>

</html>
