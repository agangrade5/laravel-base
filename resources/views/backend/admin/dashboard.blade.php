@extends('layouts.backend.app')
@section('title', $title)
@section('content')

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
             <div>
                <h4 class="page-title pt-2">Admin Dashboard</h4>
                <p class="page-subtitle text-muted mb-0">Welcome back, {{ auth()->user()->name }}!</p>
             </div>
             <div class="dashboard-date-badge px-3 py-2 rounded-3 border d-flex align-items-center gap-2">
                <i class="bi bi-calendar3 text-primary"></i>
                <span class="small fw-semibold">{{ now()->format('l, F d, Y') }}</span>
             </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">
        <!-- Metrics Boxes Row -->
        <div class="row g-4 mb-4">
              <!-- Box 1: Total Users -->
              <div class="col-lg-3 col-sm-6">
                <div class="small-box text-bg-success">
                  <div class="inner">
                    <h3>{{ $roleByCount['user'] }}</h3>
                    <p>All Registered Users</p>
                  </div>
                  <i class="bi bi-people-fill small-box-icon"></i>
                </div>
              </div>
        </div>

        <!-- Recent Activities and Action Center Row -->
        <div class="row">
            <!-- Recent System logs -->
            <div class="col-lg-8 col-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 fw-bold">Recent System Logs</h5>
                            <small class="text-muted">Latest server updates</small>
                        </div>
                        @can('activity-logs.view-all')
                            <a href="{{ route('admin.activity-logs.index') }}"
                            class="btn btn-outline-primary btn-sm px-3">
                                View All Logs
                            </a>
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="60">S.No</th>
                                        <th>User</th>
                                        <th>Activity</th>
                                        <th>Date</th>
                                        <th width="80">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentActivityLogs as $log)

                                        @php
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
                                        <tr>
                                            <td>
                                                {{ $loop->iteration }}
                                            </td>
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
                                            <td>
                                                {{ $log->description }}
                                            </td>
                                            <td>
                                                {{ \App\Helpers\UtilityHelper::formatDateTime($log->created_at) }}
                                            </td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary view-activity-log"
                                                    data-url="{{ auth()->user()->hasRole('admin')
                                                        ? route('admin.activity-logs.show', $log->id)
                                                        : route('activity-logs.show', $log->id) }}"
                                                    title="View"
                                                >
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td
                                                colspan="5"
                                                class="text-center py-4 text-muted"
                                            >
                                                No activity logs found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Quick Actions & System Overview -->
            <div class="col-lg-4 col-12">
                <!-- Quick Action & Administration Tools -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header border-0 py-3">
                        <h6 class="mb-0 fw-bold text-body-emphasis">Quick Actions</h6>
                        <small class="text-muted">Common administrative shortcuts</small>
                    </div>
                    <div class="card-body pt-0">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.users.index') }}" class="quick-action-link p-3 rounded-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="quick-action-icon bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-people-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-body-emphasis">Manage User Accounts</h6>
                                        <small class="text-muted">Create, edit, or deactivate user credentials</small>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right text-muted quick-action-arrow"></i>
                            </a>

                            <a href="{{ route('admin.settings.index') }}" class="quick-action-link p-3 rounded-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="quick-action-icon bg-secondary-subtle text-secondary rounded-3 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-gear-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-body-emphasis">System Settings</h6>
                                        <small class="text-muted">Update configuration and Change Password</small>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right text-muted quick-action-arrow"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Activity View Modal --}}
@include('backend.activity-logs.view-modal')

@endsection

@push('scripts')
    {!! \App\Helpers\UtilityHelper::returnScriptWithNonce(asset('assets/js/backend/activity-logs.js')) !!}
@endpush
