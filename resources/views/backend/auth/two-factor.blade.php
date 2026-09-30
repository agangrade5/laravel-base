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
    {{-- 2FA Card --}}
    <div class="card shadow-sm border-0">
        <div class="card-body login-card-body p-4 p-md-5">
            {{-- Security Icon --}}
            <div class="text-center mb-4">
                <div
                    class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary"
                    style="width: 64px; height: 64px;"
                >
                    <i class="bi bi-shield-lock fs-2"></i>
                </div>
            </div>

            {{-- Heading --}}
            <div class="text-center mb-4">
                <h4 class="fw-bold mb-2 two-factor-title">
                    Two-Factor Authentication
                </h4>
                <p class="mb-0 small two-factor-description">
                    Verify your identity using your authenticator app
                    to continue signing in.
                </p>
            </div>
            {{-- Information --}}
            <div class="alert alert-info d-flex align-items-start gap-2 mb-4">
                <i class="bi bi-info-circle-fill mt-1"></i>
                <div class="small">
                    Open <strong>Google Authenticator</strong> or another
                    compatible TOTP app and enter the 6-digit verification
                    code shown for your account.
                </div>
            </div>

            {{-- 2FA Form --}}
            <form
                method="POST"
                action="{{ route('admin.twoFa.verify') }}"
                id="two-factor-form"
                class="needs-validation"
                novalidate
            >

                @csrf
                <input
                    type="hidden"
                    name="timezone"
                    id="timezone"
                >

                {{-- Authentication Code --}}
                <div class="mb-4">
                    <label
                        for="one_time_password"
                        class="form-label fw-semibold"
                    >
                        Authentication Code
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-shield-lock"></i>
                        </span>
                        <input
                            type="text"
                            name="one_time_password"
                            id="one_time_password"
                            class="form-control text-center fw-semibold"
                            placeholder="000000"
                            maxlength="6"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            pattern="[0-9]{6}"
                            autofocus
                            required
                            style="
                                letter-spacing: 0.45rem;
                                font-size: 1.2rem;
                            "
                        >
                    </div>
                    <div class="form-text two-factor-title">
                        <i class="bi bi-keyboard me-1"></i>
                        Enter the 6-digit code from your authenticator app.
                    </div>
                    @error('one_time_password')
                        <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                {{-- Submit --}}
                <div class="d-grid mb-3">
                    <button
                        type="submit"
                        id="two-factor-submit"
                        class="btn btn-primary py-2"
                    >
                        <i class="bi bi-shield-check me-1"></i>
                        Verify &amp; Sign In
                    </button>
                </div>
            </form>
            {{-- Back to Login --}}
            <div class="text-center mt-3">
                <a
                    href="{{ route('admin.login') }}"
                    class="text-body-secondary text-decoration-none small"
                >
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Login
                </a>
            </div>
        </div>
    </div>

    {{-- Security Note --}}
    <div class="text-center mt-3">
        <small class="text-body-secondary">
            <i class="bi bi-lock-fill me-1"></i>
            Your authentication code is securely verified.
        </small>
    </div>
</main>
<!--end:: Main Content-->
@endsection
