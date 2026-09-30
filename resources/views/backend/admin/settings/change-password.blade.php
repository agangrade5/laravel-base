<div class="card settings-card mb-4">
    <div class="card-header d-flex align-items-center">
        <i class="bi bi-shield-lock-fill fs-4 me-2 text-primary"></i>
        <div>
            <h5 class="mb-0 fw-bold">Change Password</h5>
            <small class="text-muted">Ensure your account is using a secure password</small>
        </div>
    </div>
    <div class="card-body">
        <form
            method="POST"
            action="{{ route('admin.settings.change-password') }}"
            id="change-password-form"
            class="row g-4 needs-validation"
            novalidate
        >
            @csrf
            <div class="col-md-12">
                <label class="form-label fw-semibold text-secondary small" for="pwd-current">Current Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input
                        type="password"
                        class="form-control"
                        id="pwd-current"
                        name="current_password"
                        placeholder="Enter current password"
                        autocomplete="current-password"
                        required
                    >
                    <x-toggle-password-btn target="pwd-current" />
                </div>
                <div class="invalid-feedback" id="current-password-error"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="pwd-new">New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input
                        type="password"
                        class="form-control"
                        id="pwd-new"
                        name="password"
                        placeholder="Enter new password"
                        autocomplete="new-password"
                        required
                    >
                    <x-toggle-password-btn target="pwd-new" />
                </div>
                <div class="invalid-feedback" id="password-error"></div>
                <!-- Password Strength Visualizer -->
                <div class="mt-2">
                    <div class="d-flex gap-1 mb-1">
                        <div class="flex-grow-1 strength-meter-bar bg-secondary-subtle" id="bar-1"></div>
                        <div class="flex-grow-1 strength-meter-bar bg-secondary-subtle" id="bar-2"></div>
                        <div class="flex-grow-1 strength-meter-bar bg-secondary-subtle" id="bar-3"></div>
                        <div class="flex-grow-1 strength-meter-bar bg-secondary-subtle" id="bar-4"></div>
                    </div>
                    <small class="text-muted" id="strength-text">Password should be 8-15 characters & include 1 uppercase, a lowercase, a special character & a number.</small>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="pwd-confirm">Confirm New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input
                        type="password"
                        class="form-control"
                        id="pwd-confirm"
                        name="password_confirmation"
                        placeholder="Confirm new password"
                        autocomplete="new-password"
                        required
                    >
                    <x-toggle-password-btn target="pwd-confirm" />
                </div>
                <div class="invalid-feedback" id="password-confirmation-error"></div>
            </div>
            <div class="col-12">
                <button
                    type="submit"
                    class="btn btn-primary px-4 py-2"
                    id="change-password-btn"
                >
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
    {!! \App\Helpers\UtilityHelper::returnScriptWithNonce(asset('assets/js/backend/change-password.js')) !!}
@endpush
