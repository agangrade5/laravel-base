<div class="card settings-card mb-4">
    <div class="card-header d-flex align-items-center">
        <i class="bi bi-envelope-paper-fill fs-4 me-2 text-primary"></i>
        <div>
            <h5 class="mb-0 fw-bold">Mail (SMTP) Settings</h5>
            <small class="text-muted">Configure mail server credentials and sender details for system emails.</small>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.email') }}" id="email-settings-form" class="row g-4">
            @csrf
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="mail_mailer">Mail Driver</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-hdd-network"></i></span>
                    <select class="form-select" id="mail_mailer" name="mail_mailer">
                        <option value="smtp" {{ ($mailData['mail_mailer'] ?? 'smtp') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                        <option value="sendmail" {{ ($mailData['mail_mailer'] ?? 'smtp') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                        <option value="mailgun" {{ ($mailData['mail_mailer'] ?? 'smtp') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                        <option value="ses" {{ ($mailData['mail_mailer'] ?? 'smtp') == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                        <option value="postmark" {{ ($mailData['mail_mailer'] ?? 'smtp') == 'postmark' ? 'selected' : '' }}>Postmark</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="mail_host">Mail Host</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-globe"></i></span>
                    <input type="text" class="form-control" id="mail_host" name="mail_host" placeholder="smtp.gmail.com" value="{{ $mailData['mail_host'] ?? '' }}">
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold text-secondary small" for="mail_port">Mail Port</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-hash"></i></span>
                    <input type="text" class="form-control" id="mail_port" name="mail_port" placeholder="587" value="{{ $mailData['mail_port'] ?? '587' }}">
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold text-secondary small" for="mail_encryption">Encryption</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                    <select class="form-select" id="mail_encryption" name="mail_encryption">
                        <option value="tls" {{ ($mailData['mail_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ ($mailData['mail_encryption'] ?? 'tls') == 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="none" {{ ($mailData['mail_encryption'] ?? 'tls') == 'none' ? 'selected' : '' }}>None</option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold text-secondary small" for="mail_username">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="mail_username" name="mail_username" placeholder="Mail username" value="{{ $mailData['mail_username'] ?? '' }}">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="mail_password">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="password" class="form-control" id="mail_password" name="mail_password" placeholder="Mail password" value="{{ $mailData['mail_password_decrypted'] ?? '' }}">
                    <x-toggle-password-btn target="mail_password" />
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="mail_from_address">From Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="mail_from_address" name="mail_from_address" placeholder="noreply@example.com" value="{{ $mailData['mail_from_address'] ?? '' }}">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="mail_from_name">From Sender Name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                    <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" placeholder="Sender Name" value="{{ $mailData['mail_from_name'] ?? '' }}">
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4 py-2" id="email-settings-btn">
                    <i class="bi bi-check-lg me-1"></i> Save Email Settings
                </button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
    {!! \App\Helpers\UtilityHelper::returnScriptWithNonce(asset('assets/js/backend/email-settings.js')) !!}
@endpush
