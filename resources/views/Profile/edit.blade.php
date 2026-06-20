@extends('layouts.app')

@section('title', 'Edit Profile')

@section('sidebar-nav')
    @if (auth()->user()->role === 'teacher')
        @include('teacher.partials.sidebar-nav')
    @else
        @include('student.partials.sidebar-nav')
    @endif
@endsection

@section('page-heading')
    <h3>Edit Profile</h3>
@endsection

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="row">
        <div class="col-12 col-lg-8">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card mb-3">
                    <div class="card-body">

                        <div class="mb-4 d-flex align-items-center">
                            <div class="me-3" style="width:80px;height:80px;">
                                <img id="avatar-preview"
                                     src="{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}"
                                     class="rounded-circle w-100 h-100"
                                     style="object-fit:cover;{{ $user->avatar ? '' : 'display:none;' }}">
                                <span id="avatar-placeholder"
                                      class="d-flex align-items-center justify-content-center w-100 h-100 rounded-circle bg-danger text-white fw-bold"
                                      style="font-size:28px;{{ $user->avatar ? 'display:none;' : '' }}">
                                    {{ strtoupper(substr($user->full_name, 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <label class="btn btn-light btn-sm font-bold mb-1" for="avatar">
                                    <i class="bi bi-camera me-1"></i> Change Photo
                                </label>
                                <input type="file" name="avatar" id="avatar" accept="image/png,image/jpeg" class="d-none" onchange="previewAvatar(this)">
                                <p class="text-muted mb-0" style="font-size:12px;">JPG or PNG, max 2MB.</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-bold">Full Name</label>
                            <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $user->full_name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-bold">Email</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                            <p class="text-muted mb-0" style="font-size:12px;">Email can't be changed here.</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-bold">Bio</label>
                            <textarea name="bio" class="form-control @error('bio') is-invalid @enderror" rows="3">{{ old('bio', $user->bio) }}</textarea>
                        </div>

                        @if ($user->role === 'student')
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-bold">Program</label>
                                    <input type="text" name="program" class="form-control" value="{{ old('program', $user->program) }}" placeholder="e.g. BS Information Technology">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-bold">Year Level</label>
                                    <input type="text" name="year_level" class="form-control" value="{{ old('year_level', $user->year_level) }}" placeholder="e.g. 3rd Year">
                                </div>
                            </div>
                        @elseif ($user->role === 'teacher')
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-bold">Department</label>
                                    <input type="text" name="department" class="form-control" value="{{ old('department', $user->department) }}" placeholder="e.g. College of Information Technology">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-bold">Position / Title</label>
                                    <input type="text" name="position" class="form-control" value="{{ old('position', $user->position) }}" placeholder="e.g. Assistant Professor">
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg font-bold">
                    <i class="bi bi-check-circle me-1"></i> Save Changes
                </button>
                <a href="{{ route('profile.show') }}" class="btn btn-light btn-lg">Cancel</a>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.getElementById('avatar-preview');
                    const placeholder = document.getElementById('avatar-placeholder');
                    img.src = e.target.result;
                    img.style.display = 'block';
                    placeholder.style.display = 'none';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush
