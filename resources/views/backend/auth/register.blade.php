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

                <!-- Password field -->
                <div class="mb-3">
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
                </div>

                <!-- Password Confirmation field -->
                <div class="mb-4">
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
                </div>

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
