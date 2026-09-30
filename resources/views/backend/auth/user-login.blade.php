@extends('layouts.auth.app')
@section('title', $title)
@section('content')

<!--begin:: Main Content -->
<main class="login-box">
    <div class="login-logo">
        <a href="/">
            <img
                src="{{ asset('assets/images/backend/logo/logo-48.png') }}"
                alt="{{ config('app.name') }}"
            >
            <b>{{ config('app.name') }}</b>
        </a>
    </div>
    <!-- /.login-logo -->

    <div class="card">
        <div class="card-body login-card-body">
            <h5 class="login-box-msg">
                {{ $title }}
            </h5>

            @if ($errors->has('login'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    {{ $errors->first('login') }}

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close"
                    ></button>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('login.send-otp') }}"
                id="login-form"
                class="needs-validation"
                novalidate
            >

                @csrf

                <!-- Login Type -->
                <div class="mb-3">

                    <label class="form-label fw-semibold text-secondary-emphasis small">
                        Login Type
                    </label>

                    <div>

                        <!-- Email -->
                        <div class="form-check form-check-inline">

                            <input
                                class="form-check-input"
                                type="radio"
                                name="login_type"
                                id="login-email"
                                value="email"
                                {{ old('login_type', 'email') === 'email' ? 'checked' : '' }}
                            >

                            <label
                                class="form-check-label"
                                for="login-email"
                            >
                                Email
                            </label>

                        </div>

                        <!-- Phone -->
                        <div class="form-check form-check-inline">

                            <input
                                class="form-check-input"
                                type="radio"
                                name="login_type"
                                id="login-phone"
                                value="phone"
                                {{ old('login_type') === 'phone' ? 'checked' : '' }}
                            >

                            <label
                                class="form-check-label"
                                for="login-phone"
                            >
                                Phone Number
                            </label>

                        </div>

                    </div>

                    @error('login_type')
                        <div class="text-danger small mt-1">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <!-- Email Section -->
                <div
                    id="email-section"
                    class="mb-3"
                >
                    <label
                        for="email"
                        class="form-label fw-semibold text-secondary-emphasis small"
                    >
                        Email Address
                    </label>

                    <div class="input-group">
                        <div class="input-group-text">
                            <span class="bi bi-envelope"></span>
                        </div>

                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control"
                            placeholder="Email Address"
                            value="{{ old('email') }}"
                            autocomplete="email"
                        >

                    </div>

                    @error('email')
                        <div class="text-danger small mt-1">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <!-- Phone Section -->
                <div
                    id="phone-section"
                    class="mb-3"
                    style="display: none;"
                >
                    <label
                        for="phone"
                        class="form-label fw-semibold text-secondary-emphasis small"
                    >
                        Phone Number
                    </label>
                    <div class="input-group">

                        <!-- Country Code -->
                        @include('partials.country-code-dropdown', [
                            'idPrefix'      => 'login_',
                            'selectedCode'  => old('country_code', '+91'),
                            'buttonClass'   => 'btn border-0 bg-transparent text-white d-flex align-items-center justify-content-between h-100 px-2.5 w-100',
                            'buttonStyle'   => 'border-right: 1px solid rgba(255,255,255,.12) !important;',
                            'codeTextClass' => 'fw-semibold text-white small',
                            'flagClass'     => 'rounded-1 border border-secondary shadow-xs flex-shrink-0',
                            'showChevron'   => true,
                            'wrapperWidth'  => '110px',
                            'dropdownWidth' => '250px',
                        ])

                        <!-- Phone -->
                        <input
                            type="text"
                            name="phone"
                            id="phone"
                            class="form-control"
                            placeholder="Phone Number"
                            value="{{ old('phone') }}"
                            inputmode="numeric"
                            autocomplete="tel"
                        >
                    </div>

                    @error('phone')
                        <div class="text-danger small mt-1">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            {{ $message }}
                        </div>
                    @enderror

                    @error('country_code')
                        <div class="text-danger small mt-1">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Send OTP -->
                <div class="mb-3">
                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                        id="send-otp-btn"
                    >
                        <i class="bi bi-shield-lock me-1"></i>
                        Send OTP
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>
<!--end:: Main Content-->

@endsection

@push('scripts')
{!! \App\Helpers\UtilityHelper::returnScriptWithNonce(asset('assets/js/backend/user-login.js')) !!}
@endpush
