@extends('errors.layout')

@section('error-code', '403')

@section('content')
    <img class="img-error" src="{{ asset('assets/images/samples/error-403.png') }}" alt="Access Denied">

    <div class="error-code">403</div>
    <h1 class="error-title">Access Denied</h1>

    <p class="error-message">
        You don't have permission to view this page.<br>
        This area may be restricted to a different role, or you may have followed a link that isn't meant for your account.
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
