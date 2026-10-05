@extends('layouts.auth.app')
@section('title', $title)
@section('content')

<!--begin:: Main Content -->
<main class="register-box">
    <div class="register-logo">
        <a href="/">
            <img src="{{ asset('assets/images/backend/logo/logo-48.png') }}" alt="{{ config('app.name') }}">
            <b>{{ config('app.name') }}</b>
        </a>
    </div>
    <!-- /.register-logo -->
    <div class="card">
        <div class="card-body register-card-body">
            <h5 class="register-box-msg">{{ $title }}</h5>

            <form method="POST" action="{{ route('register.submit') }}" id="register-form" class="needs-validation" novalidate>
                @csrf

                <input type="hidden" name="timezone" id="timezone">
                <!-- Full name field -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary-emphasis small" for="name">Full Name</label>
                    <div class="input-group">
                        <div class="input-group-text">
                            <span class="bi bi-person-fill"></span>
                        </div>
                        <input
                            id="name"
                            type="text"
                            class="form-control"
                            placeholder="John Doe"
                            name="name"
                            value="{{ old('name') }}"
                            required
                        >
                    </div>
                    @error('name')
                        <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email field -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary-emphasis small" for="email">Email Address</label>
                    <div class="input-group">
                        <div class="input-group-text">
                            <span class="bi bi-envelope"></span>
                        </div>
                        <input
                            id="email"
                            type="email"
                            class="form-control"
                            placeholder="Email Address"
                            name="email"
                            value="{{ old('email') }}"
                            required
                        >
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <!-- Mobile Number field -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary-emphasis small" for="phone_number">Mobile Number</label>
                    <div class="input-group">
                        <!-- Country Code -->
                        @include('partials.country-code-dropdown', [
                            'idPrefix'      => 'register_',
                            'selectedCode'  => old('country_code', '+91'),
                            'buttonClass'   => 'btn border-0 bg-transparent text-white d-flex align-items-center justify-content-between h-100 px-2.5 w-100',
                            'buttonStyle'   => 'border-right: 1px solid rgba(255,255,255,.12) !important;',
                            'codeTextClass' => 'fw-semibold text-white small',
                            'flagClass'     => 'rounded-1 border border-secondary shadow-xs flex-shrink-0',
                            'showChevron'   => true,
                            'wrapperWidth'  => '110px',
                            'dropdownWidth' => '250px',
                        ])

                        <input
                            type="text"
                            name="phone_number"
                            id="phone_number"
                            class="form-control"
                            value="{{ old('phone_number') }}"
                            placeholder="Enter mobile number"
                            inputmode="numeric"
                            maxlength="15"
                            required
                        >
                    </div>
                    @error('country_code')
                        <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                    @error('phone_number')
                        <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password field -->
                {{-- <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary-emphasis small" for="password">Password</label>
                    <div class="input-group">
                        <div class="input-group-text">
                            <span class="bi bi-lock-fill"></span>
                        </div>
                        <input
                            id="password"
                            type="password"
                            class="form-control"
                            placeholder="••••••••"
                            name="password"
                            required
                        >
                        <x-toggle-password-btn target="password" />
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div> --}}

                <!-- Password Confirmation field -->
                {{-- <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary-emphasis small" for="password_confirmation">Confirm Password</label>
                    <div class="input-group">
                        <div class="input-group-text">
                            <span class="bi bi-lock-fill"></span>
                        </div>
                        <input
                            id="password_confirmation"
                            type="password"
                            class="form-control"
                            placeholder="••••••••"
                            name="password_confirmation"
                            required
                        >
                        <x-toggle-password-btn target="password_confirmation" />
                    </div>
                    @error('password_confirmation')
                        <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div> --}}

                <!-- Submit Button -->
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        Create Account
                    </button>
                </div>
            </form>

            <div class="text-center mt-3 pt-3 border-top border-white-50">
                <p class="mb-0">
                    <a href="{{ route('login') }}">
                        Already have an account? Sign In
                    </a>
                </p>
            </div>
        </div>
    </div>
</main>
<!--end:: Main Content-->
@endsection
