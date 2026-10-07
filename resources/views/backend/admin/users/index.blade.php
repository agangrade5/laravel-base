@extends('layouts.backend.app')
@section('title', $title)
@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="page-title pt-2">{{ $title.'s' }}</h4>
                <p class="page-subtitle text-muted mb-0">View, edit, and manage user accounts.</p>
            </div>
            <div class="dashboard-date-badge px-3 py-2 rounded-3 border d-flex align-items-center gap-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $title.'s' }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->
<!--begin::App Content-->
<div class="app-content">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <!--begin::Col-->
            <div class="col-12">
                <div class="card mb-4">
                    <!-- Card Header -->
                    <div class="card-header">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-4">
                                <h3 class="card-title">{{ $title.'s' }} List</h3>
                            </div>
                            <div class="col-12 col-md-8 text-md-end">
                                <div class="d-flex flex-wrap justify-content-md-end gap-2 align-items-center">
                                    <form method="GET" action="{{ route('admin.users.index') }}" class="settings-search-wrapper w-auto">
                                        <i class="bi bi-search"></i>
                                        <input
                                            type="search"
                                            id="user-search"
                                            name="search"
                                            class="form-control settings-search-input"
                                            placeholder="Search users"
                                            aria-label="Search users"
                                            value="{{ request('search') }}"
                                        />
                                    </form>
                                    <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                        <i class="bi bi-person-plus"></i>Add User
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card Header-->
                    <!--begin::Card Body-->
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">S.No</th>
                                        <th>User</th>
                                        <th>Mobile Number</th>
                                        <th>Role</th>
                                        <th>Registered Date</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)

                                        <tr id="user-row-{{ $user['id'] }}">
                                            <!-- Serial Number -->
                                            <td class="ps-4">
                                                {{ $users->firstItem() + $loop->index }}
                                            </td>
                                            <!-- User Info with Avatar -->
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-2">
                                                        <img
                                                            src="{{ $user['image_url'] }}"
                                                            alt="{{ $user['name'] }}"
                                                            class="img-size-32 rounded-circle"
                                                        >
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="small fw-semibold">
                                                            {{ $user['name'] }}
                                                        </div>

                                                        @if($user['email'])
                                                            <div class="small text-muted">
                                                                {{ $user['email'] }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <!-- Mobile Number -->
                                            <td>
                                                {{-- <img
                                                    src="{{ asset('assets/images/flags/' . $user['country_iso'] . '.svg') }}"
                                                    width="18"
                                                    height="14"
                                                    alt="{{ $user['country_iso'] }}"
                                                    class="rounded-1 border shadow-xs flex-shrink-0"
                                                > --}} {{ $user['country_code'] }}-{{ $user['phone_number'] }}
                                            </td>
                                            <!-- Email -->
                                            <td>
                                                <span class="badge text-bg-primary">{{ Str::ucfirst($user->getRoleNames()->first() ?? 'No Role'); }}</span>
                                            </td>
                                            <!-- Created Date -->
                                            <td>
                                                <span class="small fw-semibold text-secondary d-block"><i class="bi bi-clock me-1"></i>
                                                    {{ \App\Helpers\UtilityHelper::formatDateTime($user['created_at'], 'd M Y') }}
                                                </span>
                                                <small class="text-muted">{{ \App\Helpers\UtilityHelper::formatDateTime($user['created_at'], 'h:i:s A') }}</small>
                                            </td>
                                            <!-- Status badge -->
                                            <td>
                                                @if($user['is_active'])
                                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1">Active</span>
                                                @else
                                                    <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Inactive</span>
                                                @endif
                                            </td>
                                            <!-- Actions -->
                                            <td class="text-end pe-4">
                                                <div class="btn-group">
                                                    {{-- Edit Button --}}
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary edit-user-btn"
                                                        data-id="{{ $user['id'] }}"
                                                        data-name="{{ $user['name'] }}"
                                                        data-email="{{ $user['email'] }}"
                                                        data-phone="{{ $user['phone_number'] }}"
                                                        data-country-code="{{ $user['country_code'] ?? '+91' }}"
                                                        data-active="{{ $user['is_active'] ? 1 : 0 }}"
                                                        title="Edit User"
                                                    >
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    {{-- Delete Form --}}
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-danger delete-record-btn"
                                                        data-url="{{ route('admin.users.destroy', $user['id']) }}"
                                                        data-row-id="user-row-{{ $user['id'] }}"
                                                        data-confirm-title="Delete User?"
                                                        data-confirm-text="Are you sure you want to delete this user?"
                                                        data-confirm-button="Yes, Delete"
                                                        data-confirm-button-class="btn btn-danger"
                                                        data-confirm-cancel-button-class="btn btn-secondary"
                                                    >
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-clipboard-x fs-1 d-block mb-3"></i>
                                                    <h6 class="fw-semibold">No Users Found</h6>
                                                    <p class="mb-0 small">There are no users to display.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Pagination -->
                    <x-pagination :paginator="$users" label="users" />
                    <!--end::Pagination-->
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
@include('backend.admin.users.create')
@include('backend.admin.users.edit')

@endsection

@push('scripts')
{!! \App\Helpers\UtilityHelper::returnScriptWithNonce(asset('assets/js/backend/user.js')) !!}
@endpush
