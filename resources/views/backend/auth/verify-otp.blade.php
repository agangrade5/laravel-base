@extends('layouts.auth.app')
@section('title', $title)
@section('content')

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

    <div class="card auth-card">
        <div class="card-body p-4">
            <h4 class="text-center text-white fw-bold mb-4">
                Verify OTP
            </h4>
            <p class="text-center text-light mb-2">
                Enter the 6-digit OTP sent to you.
            </p>

            {{-- @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @error('otp')
                <div class="alert alert-danger text-center">
                    {{ $message }}
                </div>
            @enderror --}}

            <p class="text-center mb-2">
                <span class="text-light">
                    OTP expires in
                </span>
                <strong
                    id="otp-countdown"
                    class="text-info"
                >
                    --:--
                </strong>
            </p>
            <p
                class="text-center text-warning mb-4"
                id="attempt-info"
            >
                {{ $remainingAttempts }} attempts remaining
            </p>
            <form
                method="POST"
                action="{{ route('login.verify.submit') }}"
                id="otp-form"
                data-expires-at="{{ $expiresAt }}"
                data-attempts="{{ $attempts }}"
                data-max-attempts="{{ $maxAttempts }}"
            >
                @csrf

                <input type="hidden" name="timezone" id="timezone">

                <div class="otp-container d-flex justify-content-center gap-2 mb-4">
                    @for ($i = 0; $i < 6; $i++)
                        <input
                            type="text"
                            name="otp[]"
                            class="otp-input"
                            maxlength="1"
                            inputmode="numeric"
                            autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                            required
                        >
                    @endfor
                </div>
                <button
                    type="submit"
                    class="btn btn-primary w-100 py-2"
                    id="verify-otp-btn"
                >
                    Verify OTP
                </button>
            </form>
            <div
                class="text-center mt-3 d-none"
                id="resend-container"
            >
                <form
                    method="POST"
                    action="{{ route('login.resend-otp') }}"
                >
                    @csrf
                    <button
                        type="submit"
                        class="btn btn-link text-info text-decoration-none"
                    >
                        Resend OTP
                    </button>
                </form>
            </div>
            <div class="text-center mt-2">
                <a
                    href="{{ route('login') }}"
                    class="text-light text-decoration-none"
                >
                    ← Back to Login
                </a>
            </div>
        </div>
    </div>
</main>

@endsection

@push('scripts')
{!! \App\Helpers\UtilityHelper::returnScriptWithNonce(asset('assets/js/backend/verify-otp.js')) !!}
@endpush
