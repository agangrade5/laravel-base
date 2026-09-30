<div
    class="modal fade"
    id="tfa-setup-modal"
    tabindex="-1"
    aria-labelledby="tfa-setup-modal-label"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            {{-- Modal Header --}}
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <div
                        class="d-flex align-items-center justify-content-center
                               rounded-circle bg-primary-subtle text-primary me-3"
                        style="width:45px;height:45px;"
                    >
                        <i class="bi bi-shield-lock fs-5"></i>
                    </div>
                    <div>
                        <h5
                            class="modal-title fw-semibold mb-1"
                            id="tfa-setup-modal-label"
                        >
                            Set up Google Authenticator
                        </h5>

                        <small class="text-muted">
                            Secure your account with two-factor authentication
                        </small>
                    </div>
                </div>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>
            {{-- Modal Body --}}
            <div class="modal-body p-4">
                {{-- Loading --}}
                <div
                    id="tfa-setup-loading"
                    class="text-center py-5"
                    style="display:none;"
                >
                    <div
                        class="spinner-border text-primary mb-3"
                        role="status"
                    ></div>

                    <p class="text-muted mb-0">
                        Preparing your Google Authenticator setup...
                    </p>
                </div>
                {{-- Setup Content --}}
                <div id="tfa-setup-content">
                    {{-- Steps --}}
                    <div class="row g-4">
                        {{-- Step 1 --}}
                        <div class="col-lg-5">
                            <div
                                class="border rounded-3 p-4 h-100
                                       text-center bg-body-tertiary"
                            >
                                <div class="mb-3">
                                    <span class="badge text-bg-primary">
                                        Step 1
                                    </span>
                                </div>
                                <h6 class="fw-semibold">
                                    Scan QR Code
                                </h6>
                                <p class="text-muted small">
                                    Open Google Authenticator on your
                                    mobile and scan the QR code below.
                                </p>
                                <div
                                    id="tfa-qr-holder"
                                    class="d-flex align-items-center
                                           justify-content-center mx-auto
                                           border rounded-3 bg-white p-3"
                                    style="
                                        width:220px;
                                        height:220px;
                                        min-height:220px;
                                    "
                                >
                                    {{-- QR code --}}
                                </div>
                                <small class="text-muted d-block mt-3">
                                    You can also use Microsoft Authenticator
                                    or another compatible TOTP app.
                                </small>
                            </div>
                        </div>
                        {{-- Step 2 + Step 3 --}}
                        <div class="col-lg-7">
                            {{-- Step 2 --}}
                            <div
                                class="border rounded-3 p-4
                                       bg-body-tertiary mb-3"
                            >
                                <div class="mb-3">
                                    <span class="badge text-bg-secondary">
                                        Step 2
                                    </span>
                                </div>
                                <h6 class="fw-semibold mb-2">
                                    Enter setup key manually
                                </h6>
                                <p class="text-muted small">
                                    If you cannot scan the QR code,
                                    enter this key manually in your
                                    authenticator app.
                                </p>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-key"></i>
                                    </span>
                                    <input
                                        type="text"
                                        id="tfa-secret-text"
                                        class="form-control font-monospace"
                                        readonly
                                        autocomplete="off"
                                    >

                                    <button
                                        type="button"
                                        id="tfa-copy-secret"
                                        class="btn btn-outline-secondary"
                                        title="Copy secret key"
                                    >
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                                <div
                                    id="tfa-copy-message"
                                    class="text-success small mt-2"
                                    style="display:none;"
                                >
                                    <i class="bi bi-check-circle me-1"></i>
                                    Secret key copied.
                                </div>
                            </div>
                            {{-- Step 3 --}}
                            <div
                                class="border rounded-3 p-4
                                       bg-body-tertiary"
                            >
                                <div class="mb-3">
                                    <span class="badge text-bg-success">
                                        Step 3
                                    </span>
                                </div>
                                <h6 class="fw-semibold mb-2">
                                    Verify your authenticator
                                </h6>
                                <p class="text-muted small">
                                    Enter the 6-digit code currently
                                    displayed in Google Authenticator.
                                </p>
                                <div class="mb-3">
                                    <label
                                        for="tfa-confirm-code"
                                        class="form-label fw-semibold"
                                    >
                                        Authentication Code
                                    </label>
                                    <input
                                        type="text"
                                        id="tfa-confirm-code"
                                        class="form-control form-control-lg
                                               text-center font-monospace"
                                        maxlength="6"
                                        inputmode="numeric"
                                        autocomplete="one-time-code"
                                        placeholder="000000"
                                        style="letter-spacing:8px;"
                                    >
                                    <div class="form-text">
                                        Enter exactly 6 digits.
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    id="tfa-confirm-enable"
                                    class="btn btn-success w-100"
                                >
                                    <i class="bi bi-shield-check me-1"></i>
                                    Confirm &amp; Enable 2FA
                                </button>
                            </div>
                        </div>
                    </div>
                    {{-- Security Information --}}
                    <div class="alert alert-info d-flex align-items-start mt-4 mb-0">
                        <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                        <div class="small">
                            <strong>Security tip:</strong>
                            Never share your secret key or
                            authentication code with anyone.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
