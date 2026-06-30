@extends('layouts.app')

@section('title', $class->class_name . ' — Members')

@section('sidebar-nav')
    @include('teacher.partials.sidebar-nav')
@endsection

@section('page-heading')
    @include('teacher.classes.partials.class-header')
@endsection

@section('content')

    @include('teacher.classes.partials.class-nav')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-0">Class Members</h4>
                <p class="text-muted text-sm mb-0">Everyone enrolled in {{ $class->class_name }}</p>
            </div>
            <span class="badge bg-primary fs-6">{{ $members->count() + 1 }} member{{ $members->count() + 1 !== 1 ? 's' : '' }}</span>
        </div>
        <div class="card-body">
            <table class="table table-hover align-middle" id="membersTable">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th class="text-end no-sort">Action</th>
                </tr>
                </thead>
                <tbody>
                {{-- Teacher row --}}
                <tr>
                    <td>
                        <a href="{{ route('profile.show') }}" class="d-flex align-items-center gap-3 text-decoration-none text-reset">
                            <div class="avatar avatar-md">
                                @if ($class->teacher->avatar)
                                    <img src="{{ $class->teacher->avatar }}"
                                         alt="{{ $class->teacher->full_name }}"
                                         class="avatar-content rounded-circle"
                                         style="object-fit:cover; width:40px; height:40px;">
                                @else
                                    <span class="avatar-content d-flex align-items-center justify-content-center rounded-circle bg-danger text-white fw-bold"
                                          style="width:40px; height:40px; font-size:.9rem;">
                                            {{ strtoupper(substr($class->teacher->full_name ?? '?', 0, 1)) }}
                                        </span>
                                @endif
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $class->teacher->full_name }}</div>
                                <div class="text-muted" style="font-size:.78rem;">You</div>
                            </div>
                        </a>
                    </td>
                    <td class="text-muted">{{ $class->teacher->email }}</td>
                    <td><span class="badge bg-light-primary text-primary">Teacher</span></td>
                    <td class="text-muted">—</td>
                    <td></td>
                </tr>

                {{-- Student rows --}}
                @forelse ($members as $enrollment)
                    <tr>
                        <td>
                            <a href="{{ route('profile.view', $enrollment->student->user_id) }}"
                               class="d-flex align-items-center gap-3 text-decoration-none text-reset">
                                <div class="avatar avatar-md">
                                    @if ($enrollment->student->avatar)
                                        <img src="{{ $enrollment->student->avatar }}"
                                             alt="{{ $enrollment->student->full_name }}"
                                             class="avatar-content rounded-circle"
                                             style="object-fit:cover; width:40px; height:40px;">
                                    @else
                                        <span class="avatar-content d-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-bold"
                                              style="width:40px; height:40px; font-size:.9rem;">
                                                {{ strtoupper(substr($enrollment->student->full_name ?? '?', 0, 1)) }}
                                            </span>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $enrollment->student->full_name }}</div>
                                    <div class="text-muted" style="font-size:.78rem;">Student</div>
                                </div>
                            </a>
                        </td>
                        <td class="text-muted">{{ $enrollment->student->email }}</td>
                        <td><span class="badge bg-light-secondary text-secondary">Student</span></td>
                        <td class="text-muted">
                            {{ $enrollment->created_at ? $enrollment->created_at->format('M d, Y') : '—' }}
                        </td>
                        <td class="text-end">
                            <form action="{{ route('teacher.classes.members.kick', [$class->class_id, $enrollment->student->user_id]) }}"
                                  method="POST" class="d-inline kick-form">
                                @csrf
                                <input type="hidden" name="student_name" value="{{ $enrollment->student->full_name }}">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-person-dash me-1"></i> Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="bi bi-people fs-3 d-block mb-2 opacity-50"></i>
                            No students yet. Share the class code:
                            <strong class="text-dark">{{ $class->class_code }}</strong>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendors/simple-datatables/style.css') }}">
    <style>
        [data-theme="dark"] .dataTable-input,
        [data-theme="dark"] .datatable-input,
        [data-theme="dark"] input.dataTable-input,
        [data-theme="dark"] #membersTable_wrapper input[type="search"],
        [data-theme="dark"] .dataTable-top input{
            background-color: #1c2030 !important;
            border-color: #2b3047 !important;
            color: #cfd3e3 !important;
        }
        [data-theme="dark"] .dataTable-input::placeholder,
        [data-theme="dark"] .datatable-input::placeholder{
            color: #9aa0b8 !important;
        }
        [data-theme="dark"] .dataTable-selector,
        [data-theme="dark"] .datatable-selector{
            background-color: #1c2030 !important;
            border-color: #2b3047 !important;
            color: #cfd3e3 !important;
        }
        [data-theme="dark"] .dataTable-table thead th,
        [data-theme="dark"] .datatable-table thead th{
            background-color: #14171f !important;
            color: #cfd3e3 !important;
            border-color: #2b3047 !important;
        }
        [data-theme="dark"] .dataTable-table tbody tr,
        [data-theme="dark"] .datatable-table tbody tr{
            background-color: #1c2030 !important;
            color: #cfd3e3 !important;
        }
        [data-theme="dark"] .dataTable-table td,
        [data-theme="dark"] .dataTable-table th,
        [data-theme="dark"] .datatable-table td,
        [data-theme="dark"] .datatable-table th{
            border-color: #2b3047 !important;
        }
        [data-theme="dark"] .dataTable-pagination a,
        [data-theme="dark"] .datatable-pagination a{
            color: #cfd3e3 !important;
        }
        [data-theme="dark"] .dataTable-pagination a.active,
        [data-theme="dark"] .datatable-pagination a.active{
            background-color: #435ebe !important;
            color: #fff !important;
        }
        [data-theme="dark"] .dataTable-bottom,
        [data-theme="dark"] .datatable-bottom,
        [data-theme="dark"] .dataTable-info,
        [data-theme="dark"] .datatable-info{
            color: #9aa0b8 !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/vendors/simple-datatables/simple-datatables.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const table = document.querySelector('#membersTable');
            if (table) {
                new simpleDatatables.DataTable(table, {
                    perPage: 15,
                    perPageSelect: [10, 15, 25, 50],
                    columns: [
                        { select: 4, sortable: false } // Action column — no sort
                    ],
                    labels: {
                        placeholder: 'Search members...',
                        perPage: '{select} per page',
                        noRows: 'No members found',
                        info: 'Showing {start} to {end} of {rows} members',
                    }
                });
            }

            // SweetAlert2 confirm for Remove
            document.querySelectorAll('.kick-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const name = this.querySelector('[name="student_name"]').value;
                    Swal.fire({
                        title: 'Remove Student?',
                        text: `Remove ${name} from this class? They will need to rejoin.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, remove',
                        cancelButtonText: 'Cancel',
                    }).then(result => {
                        if (result.isConfirmed) this.submit();
                    });
                });
            });
        });
    </script>
@endpush
