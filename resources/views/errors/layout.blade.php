<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('error-code', 'Error') — TUP-LMS</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pages/error.css') }}">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: #ebf3ff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-wrap {
            text-align: center;
            padding: 2rem 1.5rem;
            max-width: 540px;
            width: 100%;
        }
        .error-wrap .img-error {
            max-width: 300px;
            width: 100%;
            margin: 0 auto 1.5rem;
            display: block;
        }
        .error-wrap .error-code {
            font-size: 5rem;
            font-weight: 800;
            line-height: 1;
            color: #435ebe;
            margin-bottom: 0.25rem;
        }
        .error-wrap .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3a52;
            margin-bottom: 0.75rem;
        }
        .error-wrap .error-message {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .error-wrap .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .error-wrap .error-detail {
            margin-top: 1.5rem;
            font-size: 0.8rem;
            color: #adb5bd;
        }
    </style>
</head>
<body>
<div class="error-wrap">
    @yield('content')
    <p class="error-detail">TUP-LMS &mdash; AI Learning Platform</p>
</div>
</body>
</html>
