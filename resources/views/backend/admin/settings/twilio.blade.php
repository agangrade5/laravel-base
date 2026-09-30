<div class="card settings-card mb-4">
    <div class="card-header d-flex align-items-center">
        <i class="bi bi-chat-text-fill fs-4 me-2 text-primary"></i>
        <div>
            <h5 class="mb-0 fw-bold">Twilio SMS Settings</h5>
            <small class="text-muted">Configure Twilio API credentials for sending SMS notifications and alerts.</small>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.twilio') }}" id="twilio-settings-form" class="row g-4">
            @csrf


            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="twilio_account_sid">Twilio Account SID</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="text" class="form-control font-mono twilio-field" id="twilio_account_sid" name="twilio_account_sid" placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" value="{{ $twilioData['twilio_account_sid'] ?? '' }}">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="twilio_auth_token">Twilio Auth Token</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control twilio-field" id="twilio_auth_token" name="twilio_auth_token" placeholder="Enter Twilio Auth Token" value="{{ $twilioData['twilio_auth_token_decrypted'] ?? '' }}">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="twilio_from_number">Twilio From Number</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                    <input type="text" class="form-control twilio-field" id="twilio_from_number" name="twilio_from_number" placeholder="+1234567890" value="{{ $twilioData['twilio_from_number'] ?? '' }}">
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4 py-2" id="twilio-settings-btn">
                    <i class="bi bi-check-lg me-1"></i> Save Twilio Settings
                </button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
    {!! \App\Helpers\UtilityHelper::returnScriptWithNonce(asset('assets/js/backend/twilio-settings.js')) !!}
@endpush
