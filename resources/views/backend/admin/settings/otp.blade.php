<div class="card settings-card mb-4">
    <div class="card-header d-flex align-items-center">
        <i class="bi bi-shield-fill-check fs-4 me-2 text-primary"></i>
        <div>
            <h5 class="mb-0 fw-bold">OTP Settings</h5>
            <small class="text-muted">Configure One-Time Password (OTP) verification rules, length, expiration time, and default testing values.</small>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.otp') }}" id="otp-settings-form" class="row g-4">
            @csrf

            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small">Default Static OTP</label>
                <div class="p-2 rounded-3 border d-flex align-items-center justify-content-between flex-wrap gap-2" style="min-height: 38px;">
                    <!-- <span class="fw-semibold text-body-emphasis small ms-2">Use Default OTP:</span> -->
                    <div class="d-flex align-items-center gap-3 me-2">
                        <div class="form-check mb-0">
                            <input class="form-check-input cursor-pointer" type="radio" name="otp_is_default" id="otp_is_default_true" value="1" {{ ($otpData['is_default'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold cursor-pointer text-secondary small" for="otp_is_default_true">
                                True
                            </label>
                        </div>
                        <div class="form-check mb-0">
                            <input class="form-check-input cursor-pointer" type="radio" name="otp_is_default" id="otp_is_default_false" value="0" {{ !($otpData['is_default'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold cursor-pointer text-secondary small" for="otp_is_default_false">
                                False
                            </label>
                        </div>
                    </div>
                </div>
                <small class="text-muted mt-1 d-block">Select True to enable static OTP or False to disable</small>
            </div>
            {{-- 1. Max Time --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="otp_max_time">OTP Max Expiry Time</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                    <select class="form-select" id="otp_max_time" name="otp_max_time">
                        <option value="15" {{ ($otpData['max_time'] ?? 60) == 15 ? 'selected' : '' }}>15 Seconds</option>
                        <option value="30" {{ ($otpData['max_time'] ?? 60) == 30 ? 'selected' : '' }}>30 Seconds</option>
                        <option value="60" {{ ($otpData['max_time'] ?? 60) == 60 ? 'selected' : '' }}>60 Seconds</option>
                        <option value="90" {{ ($otpData['max_time'] ?? 60) == 90 ? 'selected' : '' }}>90 Seconds</option>
                    </select>
                </div>
                <small class="text-muted mt-1 d-block">Valid values: 15, 30, 60, 90 Seconds</small>
            </div>

            {{-- 2. OTP Length --}}


            <input type="hidden" class="form-control font-mono fw-bold" id="otp_length" name="otp_length"  value="6">

            <!-- <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="otp_length">OTP Digits Length (otp_length)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-123"></i></span>
                    <select class="form-select" id="otp_length" name="otp_length">
                        <option value="4" {{ ($otpData['otp_length'] ?? 6) == 4 ? 'selected' : '' }}>4 Digits</option>
                        <option value="5" {{ ($otpData['otp_length'] ?? 6) == 5 ? 'selected' : '' }}>5 Digits</option>
                        <option value="6" {{ ($otpData['otp_length'] ?? 6) == 6 ? 'selected' : '' }}>6 Digits</option>
                    </select>
                </div>
                <small class="text-muted mt-1 d-block">Valid values: 4, 5, 6 digits</small>
            </div> -->

            {{-- 3. Is Default Radio Buttons --}}


            {{-- 4. Default Static OTP Code --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="otp_default">Default Static OTP Code</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                    <input type="text" class="form-control font-mono fw-bold" id="otp_default" name="otp_default" placeholder="e.g. {{ str_repeat('9', $otpData['otp_length'] ?? 6) }}" value="{{ $otpData['default'] ?? '999999' }}" maxlength="{{ $otpData['otp_length'] ?? 6 }}">
                </div>
                <small class="text-muted mt-1 d-block" id="otp_default_help">Static OTP code used when is_default is set to true.</small>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4 py-2" id="otp-settings-btn">
                    <i class="bi bi-check-lg me-1"></i> Save OTP Settings
                </button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
    {!! \App\Helpers\UtilityHelper::returnScriptWithNonce(asset('assets/js/backend/otp-settings.js')) !!}
@endpush
