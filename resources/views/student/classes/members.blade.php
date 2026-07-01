@extends('layouts.app')

@section('title', $class->class_name . ' — Members')

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
@endsection

@section('page-heading')
    @include('student.classes.partials.class-header')
@endsection

@section('content')

    @include('student.classes.partials.class-nav')

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
                    <th>Role</th>
                </tr>
                </thead>
                <tbody>
                {{-- Teacher row --}}
                <tr>
                    <td>
                        <a href="{{ route('profile.view', $class->teacher->user_id) }}"
                           class="d-flex align-items-center gap-3 text-decoration-none text-reset">
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
                                <div class="text-muted" style="font-size:.78rem;">{{ $class->teacher->email }}</div>
                            </div>
                        </a>
                    </td>
                    <td><span class="badge bg-light-primary text-primary">Teacher</span></td>
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
                                    <div class="text-muted" style="font-size:.78rem;">{{ $enrollment->student->email }}</div>
                                </div>
                            </a>
                        </td>
                        <td>
                            @if ($enrollment->student->user_id === auth()->id())
                                <span class="badge bg-light-success text-success">Student (you)</span>
                            @else
                                <span class="badge bg-light-secondary text-secondary">Student</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-muted py-4">
                            <i class="bi bi-people fs-3 d-block mb-2 opacity-50"></i>
                            No other students have joined yet.
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
        [data-theme="dark"] .dataTable-top input[type="search"],
        [data-theme="dark"] .dataTable-top input{
            background-color: #1a1e2c !important;
            border-color: #272d42 !important;
            color: #c8cde0 !important;
        }
        [data-theme="dark"] .dataTable-input::placeholder,
        [data-theme="dark"] .datatable-input::placeholder{
            color: #8b92ae !important;
        }
        [data-theme="dark"] .dataTable-selector,
        [data-theme="dark"] .datatable-selector{
            background-color: #1a1e2c !important;
            border-color: #272d42 !important;
            color: #c8cde0 !important;
        }
        [data-theme="dark"] .dataTable-table thead th,
        [data-theme="dark"] .datatable-table thead th{
            background-color: #13161e !important;
            color: #c8cde0 !important;
            border-color: #272d42 !important;
        }
        [data-theme="dark"] .dataTable-table tbody tr,
        [data-theme="dark"] .datatable-table tbody tr{
            background-color: #1a1e2c !important;
            color: #c8cde0 !important;
        }
        [data-theme="dark"] .dataTable-table td,
        [data-theme="dark"] .dataTable-table th,
        [data-theme="dark"] .datatable-table td,
        [data-theme="dark"] .datatable-table th{
            border-color: #272d42 !important;
        }
        [data-theme="dark"] .dataTable-bottom,
        [data-theme="dark"] .datatable-bottom,
        [data-theme="dark"] .dataTable-info{
            color: #8b92ae !important;
        }
        [data-theme="dark"] .dataTable-pagination a,
        [data-theme="dark"] .datatable-pagination a{
            color: #c8cde0 !important;
        }
        [data-theme="dark"] .dataTable-pagination a.active,
        [data-theme="dark"] .datatable-pagination a.active{
            background-color: #435ebe !important;
            color: #fff !important;
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
                    labels: {
                        placeholder: 'Search members...',
                        perPage: '{select} per page',
                        noRows: 'No members found',
                        info: 'Showing {start} to {end} of {rows} members',
                    }
                });
            }
        });
    </script>
@endpush
