@extends('layouts.app')

@section('title', 'My Classes')

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
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

    {{-- Active Classes --}}
    <div class="row">
        @forelse ($classes as $class)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h5 class="font-bold mb-0">{{ $class->class_name }}</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary border-0 px-2 py-0" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <form method="POST" action="{{ route('student.classes.archive', $class->class_id) }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-warning">
                                                <i class="bi bi-archive me-2"></i>Archive Class
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-muted mb-2" style="font-size:13px;">{{ $class->subject }}</p>
                        <p class="mb-3" style="font-size:13px;">
                            <i class="bi bi-person-video3 me-1"></i>
                            {{ $class->teacher->full_name ?? 'Unknown teacher' }}
                        </p>
                        <a href="{{ route('student.classes.show', $class->class_id) }}" class="btn btn-outline-primary btn-sm w-100 font-bold">
                            Open Class
                        </a>
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

    {{-- Archived Classes --}}
    @if ($archivedClasses->isNotEmpty())
        <div class="mt-4">
            <button class="btn btn-link text-muted px-0 mb-3 d-flex align-items-center gap-2 text-decoration-none"
                    type="button" data-bs-toggle="collapse" data-bs-target="#archivedClasses">
                <i class="bi bi-archive"></i>
                <span class="font-bold">Archived Classes ({{ $archivedClasses->count() }})</span>
                <i class="bi bi-chevron-down" id="archiveChevron"></i>
            </button>

            <div class="collapse" id="archivedClasses">
                <div class="row">
                    @foreach ($archivedClasses as $class)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card border-secondary opacity-75">
                                <div class="card-body">
                                    <h5 class="font-bold mb-1 text-muted">{{ $class->class_name }}</h5>
                                    <p class="text-muted mb-2" style="font-size:13px;">{{ $class->subject }}</p>
                                    <p class="mb-3 text-muted" style="font-size:13px;">
                                        <i class="bi bi-person-video3 me-1"></i>
                                        {{ $class->teacher->full_name ?? 'Unknown teacher' }}
                                    </p>
                                    <form method="POST" action="{{ route('student.classes.unarchive', $class->class_id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100 font-bold">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore Class
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @include('student.partials.join-class-modal')

@endsection

@push('scripts')
    <script>
        document.getElementById('archivedClasses')?.addEventListener('show.bs.collapse', function () {
            document.getElementById('archiveChevron')?.classList.replace('bi-chevron-down', 'bi-chevron-up');
        });
        document.getElementById('archivedClasses')?.addEventListener('hide.bs.collapse', function () {
            document.getElementById('archiveChevron')?.classList.replace('bi-chevron-up', 'bi-chevron-down');
        });
    </script>
@endpush
