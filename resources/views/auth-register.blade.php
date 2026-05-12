<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pages/auth.css') }}">
</head>
<body>
<div id="auth">
    <div class="row h-100">
        <div class="col-lg-5 col-12">
            <div id="auth-left">
                <div class="auth-logo">
                    <a href="{{ url('/') }}"><img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo"></a>
                </div>
                <h1 class="auth-title">Acadly</h1>
                <p class="auth-subtitle mb-5">Your course. Your AI. Your way.</p>
                <form action="#" method="POST">
                    @csrf
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input type="text" name="email" class="form-control form-control-xl" placeholder="Email">
                        <div class="form-control-icon">
                            <i class="bi bi-envelope"></i>
                        </div>
                    </div>
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input type="text" name="username" class="form-control form-control-xl" placeholder="Username">
                        <div class="form-control-icon">
                            <i class="bi bi-person"></i>
                        </div>
                    </div>
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input type="password" name="password" class="form-control form-control-xl" placeholder="Password">
                        <div class="form-control-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                    </div>
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input type="password" name="password_confirmation" class="form-control form-control-xl" placeholder="Confirm Password">
                        <div class="form-control-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                    </div>

                    <!-- Role Selection -->
                    <div class="form-group mb-4">
                        <label class="form-label text-gray-600 fw-bold mb-2">I am a:</label>
                        <div class="d-flex gap-4">
                            <div class="form-check form-check-primary">
                                <input class="form-check-input" type="radio" name="role" id="roleStudent" value="student" checked>
                                <label class="form-check-label" for="roleStudent">
                                    <i class="bi bi-mortarboard me-1"></i> Student
                                </label>
                            </div>
                            <div class="form-check form-check-primary">
                                <input class="form-check-input" type="radio" name="role" id="roleTeacher" value="teacher">
                                <label class="form-check-label" for="roleTeacher">
                                    <i class="bi bi-person-video3 me-1"></i> Teacher
                                </label>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-primary btn-block btn-lg shadow-lg mt-5">Sign Up</button>
                </form>
                <div class="text-center mt-5 text-lg fs-4">
                    <p class="text-gray-600">Already have an account? <a href="{{ route('login') }}" class="font-bold">Log in</a>.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-7 d-none d-lg-block">
            <div id="auth-right"></div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
