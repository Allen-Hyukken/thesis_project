@extends('errors.layout')

@section('error-code', '500')

@section('content')
    <img class="img-error" src="{{ asset('assets/images/samples/error-500.png') }}" alt="Server Error">

    <div class="error-code">500</div>
    <h1 class="error-title">Something Went Wrong</h1>

    <p class="error-message">
        The server ran into an unexpected problem and couldn't complete your request.<br>
        This could be a temporary issue. Please wait a moment and try refreshing the page.<br>
        If the problem keeps happening, contact your system administrator.
    </p>

    <a href="javascript:location.reload()" class="btn btn-lg btn-outline-danger btn-home">
        <i class="bi bi-arrow-clockwise"></i> Try Again
    </a>
@endsection
