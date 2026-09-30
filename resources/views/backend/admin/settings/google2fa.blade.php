<div class="card settings-card mb-4">
    <div class="card-header d-flex align-items-center">
        <i class="bi bi-shield-lock-fill fs-4 me-2 text-primary"></i>
        <div>
            <h5 class="mb-0 fw-bold">Two-Factor Authentication</h5>
            <small class="text-muted">Add an extra layer of protection to your account</small>
        </div>
    </div>
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            {{-- Google 2FA Disable --}}
            <div
                id="tfa-disable-block"
                style="{{ auth()->user()->google2fa_enabled ? '' : 'display:none;' }}"
            >

                <div class="d-flex align-items-start gap-3">
                    {{-- Icon --}}
                    <div
                        class="d-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger flex-shrink-0"
                        style="width: 48px; height: 48px;"
                    >
                        <i class="bi bi-shield-lock fs-4"></i>
                    </div>
                    {{-- Content --}}
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h6 class="mb-0 fw-semibold">
                                Google Authenticator is enabled
                            </h6>

                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>
                                Active
                            </span>
                        </div>
                        <p class="text-body-secondary small mb-3">
                            Your account is protected with Google Authenticator.
                            You will be required to enter a 6-digit authentication
                            code after entering your password during login.
                        </p>
                        {{-- Warning --}}
                        <div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
                            <i class="bi bi-exclamation-triangle-fill mt-1"></i>

                            <div class="small">
                                <strong>Important:</strong>
                                Disabling Google 2FA will reduce the security of your
                                account. You will only need your email/username and
                                password to log in.
                            </div>
                        </div>
                        {{-- Disable Form --}}
                        <form id="tfa-disable-form" novalidate data-no-loader>
                            @csrf
                            <div class="row align-items-end g-3">
                                <div class="col-md-7 col-lg-6">
                                    <label
                                        for="tfa-disable-password"
                                        class="form-label fw-semibold"
                                    >
                                        Confirm your password
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-lock-fill"></i>
                                        </span>
                                        <input
                                            type="password"
                                            id="tfa-disable-password"
                                            name="password"
                                            class="form-control"
                                            placeholder="Enter your current password"
                                            autocomplete="current-password"
                                            required
                                        >
                                        <x-toggle-password-btn target="tfa-disable-password" />
                                    </div>
                                    <div class="invalid-feedback">
                                        Please enter your current password.
                                    </div>
                                </div>
                                <div class="col-md-5 col-lg-4">
                                    <button
                                        type="submit"
                                        id="tfa-disable-btn"
                                        class="btn btn-outline-danger w-100"
                                    >
                                        <i class="bi bi-shield-x me-1"></i>
                                        Disable Google 2FA
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Google 2FA Enable --}}
            <div
                id="tfa-enable-block"
                style="{{ auth()->user()->google2fa_enabled ? 'display:none;' : '' }}"
            >
                <div class="d-flex align-items-start gap-3">
                    <div
                        class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary flex-shrink-0"
                        style="width: 48px; height: 48px;"
                    >
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-semibold mb-1">
                            Protect your account with Google Authenticator
                        </h6>
                        <p class="text-body-secondary small mb-3">
                            Add an extra layer of security to your account.
                            After entering your password, you'll be asked for
                            a 6-digit code from your authenticator app.
                        </p>
                        <button
                            id="tfa-start-setup"
                            type="button"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-shield-plus me-1"></i>
                            Enable Google 2FA
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    {!! \App\Helpers\UtilityHelper::returnScriptWithNonce(asset('assets/js/backend/google2fa-settings.js')) !!}
@endpush
