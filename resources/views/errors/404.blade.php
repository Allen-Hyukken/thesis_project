@extends('errors.layout')

@section('error-code', '404')

@section('content')
    <img class="img-error" src="{{ asset('assets/images/samples/error-404.png') }}" alt="Page Not Found">

    <div class="error-code">404</div>
    <h1 class="error-title">Page Not Found</h1>

    <p class="error-message">
        The page you're looking for doesn't exist or may have been moved.<br>
        Double-check the URL, or head back to where you came from.
    </p>

    @auth
        @if(auth()->user()->role === 'teacher')
            <a href="{{ route('teacher.dashboard') }}" class="btn btn-lg btn-outline-primary btn-home">
                <i class="bi bi-house-door"></i> Back to Dashboard
            </a>
        @else
            <a href="{{ route('student.dashboard') }}" class="btn btn-lg btn-outline-primary btn-home">
                <i class="bi bi-house-door"></i> Back to Dashboard
            </a>
        @endif
    @else
        <a href="{{ route('login') }}" class="btn btn-lg btn-outline-primary btn-home">
            <i class="bi bi-box-arrow-in-right"></i> Go to Login
        </a>
    @endauth
@endsection
