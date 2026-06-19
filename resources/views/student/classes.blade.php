@extends('layouts.app')

@section('title', 'My Classes')

@section('sidebar-nav')
    <li class="sidebar-title">Main</li>

    <li class="sidebar-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
        <a href="{{ route('student.dashboard') }}" class="sidebar-link">
            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <li class="sidebar-title">Learning</li>

    <li class="sidebar-item {{ request()->routeIs('student.classes') ? 'active' : '' }}">
        <a href="{{ route('student.classes') }}" class="sidebar-link">
            <i class="bi bi-book-half"></i>
            <span>My Enrolled Classes</span>
        </a>
    </li>

    <li class="sidebar-item">
        <a href="{{ route('student.courses') }}" class="sidebar-link">
            <i class="bi bi-journal-text"></i>
            <span>Browse Courses</span>
        </a>
    </li>

    <li class="sidebar-title">AI Study Tools</li>

    <li class="sidebar-item">
        <a href="{{ route('student.flashcards') }}" class="sidebar-link">
            <i class="bi bi-layers-fill"></i>
            <span>AI Flashcards</span>
        </a>
    </li>

    <li class="sidebar-item">
        <a href="{{ route('student.ai-tutor') }}" class="sidebar-link">
            <i class="bi bi-chat-quote-fill"></i>
            <span>AI Tutor Chat</span>
        </a>
    </li>

    <li class="sidebar-title">Account</li>

    <li class="sidebar-item">
        <a href="#" class="sidebar-link">
            <i class="bi bi-person-circle"></i>
            <span>My Profile</span>
        </a>
    </li>

    <li class="sidebar-item">
        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form-s').submit();"
           class="sidebar-link">
            <i class="bi bi-box-arrow-left"></i>
            <span>Logout</span>
        </a>
        <form id="logout-form-s" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </li>
@endsection

@section('page-heading')
    <div class="d-flex justify-content-between align-items-center">
        <h3>My Classes</h3>
        <button type="button" class="btn btn-primary font-bold" data-bs-toggle="modal" data-bs-target="#joinClassModal">
            <i class="bi bi-plus-circle me-1"></i> Join a Class
        </button>
    </div>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        @forelse ($classes as $class)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="font-bold mb-1">{{ $class->class_name }}</h5>
                        <p class="text-muted mb-2" style="font-size:13px;">{{ $class->subject }}</p>
                        <p class="mb-0" style="font-size:13px;">
                            <i class="bi bi-person-video3 me-1"></i>
                            {{ $class->teacher->full_name ?? 'Unknown teacher' }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-journal-plus fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="font-bold">You haven't joined any classes yet</h5>
                        <p class="text-muted mb-0">Ask your teacher for a class code and join above.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @include('student.partials.join-class-modal')

@endsection
