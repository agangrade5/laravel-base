@extends('layouts.auth.app')
@section('title', $title)
@section('content')

<!--begin:: Main Content -->
<main class="login-box">
    <div class="login-logo">
        <a href="/">
            <img src="{{ asset('assets/images/backend/logo/logo-48.png') }}" alt="{{ config('app.name') }}">
            <b>{{ config('app.name') }}</b>
        </a>
    </div>
    <!-- /.login-logo -->
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg mb-1">
                Forgot your password?
            </p>

            <p class="login-box-msg mb-1 fs-6">
                Enter your email address and we will send you a
                password reset link.
            </p>

            <form method="POST" action="{{ route('admin.password.email') }}" class="auth-submit-form" class="needs-validation" novalidate>
                @csrf

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

                <!-- Submit Button -->
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        Send Reset Link
                    </button>
                </div>
            </form>
            <div class="text-center mt-3 pt-3 border-top border-white-50">
                <p class="mb-1">
                    <a href="{{ route('admin.login') }}">Back to Login</a>
                </p>
            </div>
        </div>
        <!-- /.login-card-body -->
    </div>
</main>
<!--end:: Main Content-->
@endsection
