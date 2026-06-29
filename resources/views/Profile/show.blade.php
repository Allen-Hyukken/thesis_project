@extends('layouts.app')

@section('title', 'My Profile')

@section('sidebar-nav')
    @if (auth()->user()->role === 'teacher')
        @include('teacher.partials.sidebar-nav')
    @else
        @include('student.partials.sidebar-nav')
    @endif
@endsection

@section('page-heading')
    <div class="d-flex justify-content-between align-items-center">
        <h3>{{ ($isOwnProfile ?? false) ? 'My Profile' : $user->full_name . "'s Profile" }}</h3>
        @if ($isOwnProfile ?? false)
            <a href="{{ route('profile.edit') }}" class="btn btn-primary font-bold">
                <i class="bi bi-pencil-square me-1"></i> Edit Profile
            </a>
        @endif
    </div>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mx-auto mb-3" style="width:100px;height:100px;position:relative;">
                        @if ($user->avatar)
                            <img src="{{ $user->avatar }}" alt="Avatar"
                                 class="rounded-circle w-100 h-100"
                                 style="object-fit:cover;object-position:top center;"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <span class="d-none align-items-center justify-content-center w-100 h-100 rounded-circle bg-danger text-white fw-bold"
                                  style="font-size:36px;position:absolute;top:0;left:0;">
                                {{ strtoupper(substr($user->full_name, 0, 1)) }}
                            </span>
                        @else
                            <span class="d-flex align-items-center justify-content-center w-100 h-100 rounded-circle bg-danger text-white fw-bold" style="font-size:36px;">
                                {{ strtoupper(substr($user->full_name, 0, 1)) }}
                            </span>
                        @endif
                    </div>
                    <h5 class="font-bold mb-0">{{ $user->full_name }}</h5>
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                    <span class="badge {{ $user->role === 'teacher' ? 'bg-primary' : 'bg-secondary' }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4>About</h4>
                </div>
                <div class="card-body">
                    <p class="mb-4">{{ $user->bio ?: 'No bio added yet.' }}</p>

                    <div class="row">
                        @if ($user->role === 'student')
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted font-semibold mb-1">Program</h6>
                                <p class="mb-0">{{ $user->program ?: '—' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted font-semibold mb-1">Year Level</h6>
                                <p class="mb-0">{{ $user->year_level ?: '—' }}</p>
                            </div>
                        @elseif ($user->role === 'teacher')
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted font-semibold mb-1">Department</h6>
                                <p class="mb-0">{{ $user->department ?: '—' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted font-semibold mb-1">Position / Title</h6>
                                <p class="mb-0">{{ $user->position ?: '—' }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
