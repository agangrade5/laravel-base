@extends('layouts.backend.app')
@section('title', $title)
@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="page-title pt-2">{{ $title }}</h4>
                <p class="page-subtitle text-muted mb-0">Track user activities and system actions.</p>
            </div>
            <div class="dashboard-date-badge px-3 py-2 rounded-3 border d-flex align-items-center gap-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
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
                <!--begin::Card-->
                <div class="card mb-4">
                    <!--begin::Card Header-->
                    <div class="card-header">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-4">
                                <h3 class="card-title">{{ $title }} List</h3>
                            </div>
                            <div class="col-12 col-md-8 text-md-end">
                                <div class="d-flex flex-wrap justify-content-md-end gap-2 align-items-center">
                                    <div class="settings-search-wrapper w-auto">
                                        <i class="bi bi-search"></i>
                                        <input
                                            type="search"
                                            id="activity-log-search"
                                            name="search"
                                            class="form-control settings-search-input"
                                            placeholder="Search activity logs"
                                            aria-label="Search activity logs"
                                            style="width: 220px"
                                            value="{{ request('search') }}"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card Header-->
                    <!--begin::Card Body-->
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle m-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">S.No</th>
                                        <th>User</th>
                                        <th>Log Name</th>
                                        {{-- <th>Event</th> --}}
                                        <th>Description</th>
                                        <th>Date & Time</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($logs as $log)
                                    @php
                                        $isAdmin = auth()->user()->hasRole('admin');

                                        $showUrl = $isAdmin
                                            ? route('admin.activity-logs.show', $log->id)
                                            : route('activity-logs.show', $log->id);

                                        $deleteUrl = $isAdmin
                                            ? route('admin.activity-logs.destroy', $log->id)
                                            : null;

                                        $eventClass = match($log->event) {
                                            'created' => 'success',
                                            'updated' => 'primary',
                                            'deleted' => 'danger',
                                            'login' => 'info',
                                            'logout' => 'warning',
                                            default => 'secondary',
                                        };
                                        $eventIcon = match($log->event) {
                                            'created' => 'bi-plus-circle',
                                            'updated' => 'bi-pencil-square',
                                            'deleted' => 'bi-trash',
                                            'login' => 'bi-box-arrow-in-right',
                                            'logout' => 'bi-box-arrow-right',
                                            default => 'bi-activity',
                                        };
                                    @endphp
                                    <tr id="activity-log-row-{{ $log->id }}">
                                        <td class="ps-4">
                                            {{ $logs->firstItem() + $loop->index }}
                                        </td>
                                        <!-- User -->
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 me-2">
                                                    <img
                                                        src="{{ $log->causer?->image
                                                            ? Storage::disk(config('filesystems.default'))->url($log->causer->image)
                                                            : asset('assets/images/backend/user2-160x160.jpg') }}"
                                                        alt="{{ $log->causer?->name ?? 'System' }}"
                                                        class="img-size-32 rounded-circle"
                                                    >
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="small fw-semibold">
                                                        {{ $log->causer?->name ?? 'System' }}
                                                    </div>

                                                    @if($log->causer?->email)
                                                        <div class="small text-muted">
                                                            {{ $log->causer->email }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <!-- Log Name -->
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                {{ $log->log_name ?? 'default' }}
                                            </span>
                                        </td>
                                        <!-- Event -->
                                        {{-- <td>
                                            <span
                                                class="badge bg-{{ $eventClass }}-subtle text-{{ $eventClass }}"
                                            >
                                                <i class="bi {{ $eventIcon }} me-1"></i>
                                                {{ ucfirst($log->event ?? 'activity') }}
                                            </span>
                                        </td> --}}
                                        <!-- Description -->
                                        <td>
                                            <div
                                                class="small text-truncate"
                                                style="max-width: 350px"
                                                title="{{ $log->description }}"
                                            >
                                                {{ $log->description }}
                                            </div>
                                        </td>
                                        <!-- Created Date -->
                                        <td>
                                            <span class="small fw-semibold text-secondary d-block"><i class="bi bi-clock me-1"></i>
                                                {{ \App\Helpers\UtilityHelper::formatDateTime($log->created_at, 'd M Y') }}
                                            </span>
                                            <small class="small text-muted">{{ \App\Helpers\UtilityHelper::formatDateTime($log->created_at, 'h:i:s A') }}</small>
                                        </td>
                                        <!-- Actions -->
                                        <td class="text-end pe-4">
                                            <div class="btn-group">
                                                <!-- View -->
                                                @can('activity-logs.view')
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary view-activity-log"
                                                        data-url="{{ $showUrl }}"
                                                        title="View Activity"
                                                    >
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                @endcan

                                                <!-- Delete - Admin Only -->
                                                @can('activity-logs.delete')
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-danger delete-record-btn"
                                                        data-url="{{ $deleteUrl }}"
                                                        data-row-id="activity-log-row-{{ $log->id }}"
                                                        data-confirm-title="Delete Activity Log?"
                                                        data-confirm-text="Are you sure you want to delete this activity log?"
                                                        data-confirm-button="Yes, Delete"
                                                        data-confirm-button-class="btn btn-danger"
                                                        data-confirm-cancel-button-class="btn btn-secondary"
                                                    >
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-clipboard-x fs-1 d-block mb-3"></i>
                                                    <h6 class="fw-semibold">No Activity Logs Found</h6>
                                                    <p class="mb-0 small">There are no activities to display.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <!-- /.table-responsive -->
                    </div>
                    <!--end::Card Body-->
                    <!-- Pagination -->
                    <x-pagination :paginator="$logs" label="activity logs" />
                    <!--end::Pagination-->
                </div>
                <!--end::Card-->
            </div>
            <!-- /.col -->
        </div>
        <!--end::Row-->
    </div>
    <!--end::Container-->
</div>
<!--end::App Content-->

{{-- Activity View Modal --}}
@include('backend.activity-logs.view-modal')

@endsection

@push('scripts')
{!! \App\Helpers\UtilityHelper::returnScriptWithNonce(asset('assets/js/backend/activity-logs.js')) !!}
@endpush
