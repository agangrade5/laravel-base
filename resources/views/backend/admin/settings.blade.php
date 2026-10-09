@extends('layouts.backend.app')
@section('title', $title)
@section('content')

@php $isAdmin = auth()->user()->hasRole('admin'); @endphp
<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
             <div>
                <h4 class="page-title pt-2">System Settings</h4>
                <p class="page-subtitle text-muted mb-0">Configure system parameters, user profile, security, and cloud services.</p>
             </div>
             <div class="dashboard-date-badge px-3 py-2 rounded-3 border d-flex align-items-center gap-2">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb float-sm-end mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">System Settings</li>
                  </ol>
                </nav>
             </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">
        <div class="row g-4">

            <!-- Left Navigation Sidebar -->
            <div class="col-lg-3 col-md-4">
                <div class="card settings-card mb-4">
                    <div class="card-body p-3">
                        <div class="settings-sidebar-nav nav flex-column" id="settings-nav" role="tablist">
                            <a href="#account" class="settings-nav-link active mb-1" data-bs-toggle="pill" role="tab" aria-selected="true">
                                <i class="bi bi-person me-2"></i>Account Settings
                            </a>
                            @role('admin')
                                <a href="#general-setting" class="settings-nav-link mb-1" data-bs-toggle="pill" role="tab" aria-selected="false">
                                    <i class="bi bi-sliders me-2"></i> General & Actions
                                </a>
                                <a href="#change-password" class="settings-nav-link mb-1" data-bs-toggle="pill" role="tab" aria-selected="false">
                                    <i class="bi bi-key-fill me-2"></i>Change Password
                                </a>
                                <a href="#google2fa" class="settings-nav-link mb-1" data-bs-toggle="pill" role="tab" aria-selected="false">
                                    <i class="bi bi-shield-lock-fill me-2"></i>Two-Factor Auth
                                </a>
                                <a href="#email-setting" class="settings-nav-link mb-1" data-bs-toggle="pill" role="tab" aria-selected="false">
                                    <i class="bi bi-envelope-at-fill me-2"></i>Mail (SMTP)
                                </a>
                                <a href="#otp-setting" class="settings-nav-link mb-1" data-bs-toggle="pill" role="tab" aria-selected="false">
                                    <i class="bi bi-phone me-2"></i>OTP Configuration
                                </a>
                                <a href="#twilio-setting" class="settings-nav-link mb-1" data-bs-toggle="pill" role="tab" aria-selected="false">
                                    <i class="bi bi-chat-dots-fill me-2"></i>Twilio SMS
                                </a>
                                <a href="#aws-setting" class="settings-nav-link mb-1" data-bs-toggle="pill" role="tab" aria-selected="false">
                                    <i class="bi bi-cloud-arrow-up-fill me-2"></i>AWS Cloud
                                </a>
                            @endrole
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="col-lg-9 col-md-8">
                <div class="tab-content">
                    <!-- Account Tab -->
                    <div class="tab-pane fade show active" id="account" role="tabpanel">
                        @include('backend.admin.settings.account')
                    </div>
                    <!-- General Setting Tab -->
                    <div class="tab-pane fade" id="general-setting" role="tabpanel">
                        @include('backend.admin.settings.general')
                    </div>
                    <!-- Change Password Tab -->
                    <div class="tab-pane fade" id="change-password" role="tabpanel">
                        @include('backend.admin.settings.change-password')
                    </div>
                    <!-- Two-Factor Auth Tab -->
                    <div class="tab-pane fade" id="google2fa" role="tabpanel">
                        @include('backend.admin.settings.google2fa')
                    </div>
                    <!-- Email Setting Tab -->
                    <div class="tab-pane fade" id="email-setting" role="tabpanel">
                        @include('backend.admin.settings.email')
                    </div>
                    <!-- OTP Setting Tab -->
                    <div class="tab-pane fade" id="otp-setting" role="tabpanel">
                        @include('backend.admin.settings.otp')
                    </div>
                    <!-- Twilio Setting Tab -->
                    <div class="tab-pane fade" id="twilio-setting" role="tabpanel">
                        @include('backend.admin.settings.twilio')
                    </div>
                    <!-- AWS Setting Tab -->
                    <div class="tab-pane fade" id="aws-setting" role="tabpanel">
                        @include('backend.admin.settings.aws')
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
{{-- Crop Profile Image Modal --}}
@include('backend.admin.settings.modal.crop-profile-image')
{{-- Google 2FA Setup Modal --}}
@include('backend.admin.settings.modal.google2fa-setup')

@endsection
