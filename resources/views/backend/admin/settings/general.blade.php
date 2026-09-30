<div class="card settings-card mb-4">
    <div class="card-header d-flex align-items-center">
        <i class="bi bi-gear-fill fs-4 me-2 text-primary"></i>
        <div>
            <h5 class="mb-0 fw-bold">General Settings</h5>
            <small class="text-muted">Configure general system settings</small>
        </div>
    </div>
    <div class="card-body">

        {{-- Pagination Limit --}}
        <div class="row align-items-center mb-4 pb-4 border-bottom">
            <label class="col-sm-4 col-form-label fw-semibold">Default Pagination Limit</label>
            <div class="col-sm-5">
                <select
                    name="pagination_limit"
                    id="pagination-limit-select"
                    class="form-select"
                    data-url="{{ route('admin.settings.general') }}"
                >
                    @foreach(config('constants.general_options.pagination_limit', []) as $limit)
                        <option value="{{ $limit }}" {{ (int) ($generalData['pagination_limit'] ?? 10) === $limit ? 'selected' : '' }}>
                            {{ $limit }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <span id="pagination-limit-status" class="small text-muted"></span>
            </div>
        </div>

        {{-- Password Reset Link Expiry --}}
        <div class="row align-items-center mb-4 pb-4 border-bottom">
            <label class="col-sm-4 col-form-label fw-semibold">Password Reset Link Expiry</label>
            <div class="col-sm-5">
                <select
                    name="password_reset_expiry"
                    id="password-reset-expiry-select"
                    class="form-select"
                    data-url="{{ route('admin.settings.general') }}"
                >
                    @foreach(config('constants.general_options.password_reset_expiry', []) as $minutes => $label)
                        <option value="{{ $minutes }}" {{ (int) ($generalData['password_reset_expiry'] ?? 60) === $minutes ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <span id="password-reset-expiry-status" class="small text-muted"></span>
            </div>
        </div>

        {{-- Maintenance Mode --}}
        <div class="row align-items-center mb-4 pb-4 border-bottom">
            <label class="col-sm-4 col-form-label fw-semibold">Maintenance Mode</label>
            <div class="col-sm-5">
                <select
                    name="maintenance_mode"
                    id="maintenance-mode-select"
                    class="form-select"
                    data-url="{{ route('admin.settings.maintenanceMode') }}"
                >
                    <option value="0" {{ !app()->isDownForMaintenance() ? 'selected' : '' }}>Off (Live)</option>
                    <option value="1" {{ app()->isDownForMaintenance() ? 'selected' : '' }}>On (Maintenance)</option>
                </select>
            </div>
            <div class="col-sm-3">
                <span id="maintenance-mode-status" class="small text-muted"></span>
            </div>
        </div>

        {{-- System Maintenance Buttons --}}
        <div class="row g-2">
            <label class="col-12 fw-semibold mb-2">System Maintenance</label>

            <div class="col-sm-3">
                <button type="button" class="btn btn-outline-secondary w-100 system-action-btn"
                    data-url="{{ route('admin.settings.optimizeClear') }}"
                    data-confirm-title="Clear Optimization Cache?"
                    data-confirm-text="This will clear config, route, view and application cache."
                    data-confirm-icon="warning"
                    data-confirm-button="Yes, Clear"
                    data-confirm-button-class="btn btn-primary"
                    data-confirm-cancel-button-class="btn btn-secondary">
                    <i class="bi bi-arrow-repeat me-1"></i> Optimize Clear
                </button>
            </div>

            <div class="col-sm-3">
                <button type="button" class="btn btn-outline-secondary w-100 system-action-btn"
                    data-url="{{ route('admin.settings.configCache') }}"
                    data-confirm-title="Cache Configuration?"
                    data-confirm-text="This will cache the current config files."
                    data-confirm-button="Yes, Cache"
                    data-confirm-button-class="btn btn-primary"
                    data-confirm-cancel-button-class="btn btn-secondary">
                    <i class="bi bi-hdd-stack me-1"></i> Config Cache
                </button>
            </div>

            <div class="col-sm-3">
                <button type="button" class="btn btn-outline-warning w-100 system-action-btn"
                    data-url="{{ route('admin.settings.migrate') }}"
                    data-confirm-title="Run Migrations?"
                    data-confirm-text="This will run pending database migrations."
                    data-confirm-button="Yes, Migrate"
                    data-confirm-button-class="btn btn-warning"
                    data-confirm-cancel-button-class="btn btn-secondary">
                    <i class="bi bi-database-add me-1"></i> Run Migrate
                </button>
            </div>

            <div class="col-sm-3">
                <button type="button" class="btn btn-outline-danger w-100 system-action-btn"
                    data-url="{{ route('admin.settings.migrateFreshSeed') }}"
                    data-confirm-title="Run Migrate Fresh + Seed?"
                    data-confirm-text="⚠ This will DROP ALL TABLES, re-run migrations, and seed the database with default data. This cannot be undone!"
                    data-confirm-button="Yes, I understand"
                    data-confirm-button-class="btn btn-danger"
                    data-confirm-cancel-button-class="btn btn-secondary">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Migrate Fresh + Seed
                </button>
            </div>
        </div>

    </div>
</div>
@push('scripts')
    {!! \App\Helpers\UtilityHelper::returnScriptWithNonce(asset('assets/js/backend/general-settings.js')) !!}
@endpush
