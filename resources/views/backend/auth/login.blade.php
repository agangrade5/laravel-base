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
            <h5 class="login-box-msg">{{ $title }}</h5>

            <form method="POST" action="{{ route('admin.login.submit') }}" id="login-form" class="needs-validation" novalidate>
                @csrf

                <input type="hidden" name="timezone" id="timezone">
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
                <div class="mb-4">
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

                <!-- Remember & Submit -->
                <div class="row align-items-center mb-4">
                    <div class="col-7">
                        <div class="form-check">
                            <input
                                id="remember"
                                type="checkbox"
                                class="form-check-input"
                                name="remember"
                                value="1"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="remember">
                                Remember Me
                            </label>
                        </div>
                    </div>
                    <div class="col-5">
                        <button type="submit" class="btn btn-primary w-100">
                            Sign In
                        </button>
                    </div>
                </div>
            </form>

            <div class="text-center mt-3 pt-3 border-top border-white-50">
                <p class="mb-1">
                    <a href="{{ route('admin.password.request') }}">I forgot my password</a>
                </p>
            </div>
        </div>
    </div>
</main>
<!--end:: Main Content-->
@endsection
