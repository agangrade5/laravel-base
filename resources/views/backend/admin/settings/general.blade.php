<div class="card settings-card mb-4">
    <div class="card-header d-flex align-items-center">
        <h5 class="mb-0 fw-bold"><i class="bi bi-sliders text-primary me-2"></i> General System Configuration</h5>
    </div>
    <div class="card-body">
        <!-- Default Pagination Limit -->
        <div class="mb-3">
            <label for="pagination_limit" class="form-label fw-semibold">Default Pagination Limit</label>
            <select
                name="pagination_limit"
                id="pagination-limit-select"
                class="form-select"
                data-url="{{ route('admin.settings.general') }}"
            >
                @foreach(config('constants.general_options.pagination_limit', []) as $limit => $labelOne)
                    <option value="{{ $limit }}" {{ (int) ($generalData['pagination_limit'] ?? 10) === $limit ? 'selected' : '' }}>
                        {{ $labelOne }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Controls how many rows appear on table listings.</small>
            {{-- <span id="pagination-limit-status" class="small text-muted"></span> --}}
        </div>

        {{-- Password Reset Link Expiry --}}
        <div class="mb-3">
            <label for="password_reset_expiry" class="form-label fw-semibold">Password Reset Link Expiry</label>
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
            <small class="text-muted">Expiration duration for password reset tokens generated via email.</small>
            {{-- <span id="password-reset-expiry-status" class="small text-muted"></span> --}}
        </div>

        {{-- Maintenance Mode --}}
        <div class="mb-3">
            <label for="maintenance_mode" class="form-label fw-semibold">Maintenance Mode</label>
            <select
                name="maintenance_mode"
                id="maintenance-mode-select"
                class="form-select"
                data-url="{{ route('admin.settings.maintenanceMode') }}"
            >
                <option value="0" {{ !app()->isDownForMaintenance() ? 'selected' : '' }}>Off (Live)</option>
                <option value="1" {{ app()->isDownForMaintenance() ? 'selected' : '' }}>On (Maintenance)</option>
            </select>
            <small class="text-muted">Enables or disables maintenance mode.</small>
            {{-- <span id="maintenance-mode-status" class="small text-muted"></span> --}}
        </div>
    </div>
</div>
<!-- System Maintenance Action Buttons (Optimize Clear, Config Cache, Run Migrate, Migrate Fresh + Seed) -->
<div class="card settings-card mb-4">
    <div class="card-header bg-transparent border-bottom py-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-tools text-danger me-2"></i> System Artisan Runners</h5>
        <small class="text-muted">Execute vital backend CLI commands directly through secure authenticated triggers</small>
    </div>

    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6 col-lg-3">
                <button type="button" class="btn btn-outline-primary w-100 py-3 system-action-btn"
                    data-url="{{ route('admin.settings.optimizeClear') }}"
                    data-confirm-title="Clear Optimization Cache?"
                    data-confirm-text="This will clear config, route, view and application cache."
                    data-confirm-icon="question"
                    data-confirm-button="Yes, Clear"
                    data-confirm-button-class="btn btn-primary"
                    data-confirm-cancel-button-class="btn btn-secondary">
                    <i class="bi bi-arrow-repeat fs-4 d-block mb-1"></i>
                    <strong>Optimize Clear</strong>
                    <div class="small text-muted mt-1">Clear all caches</div>
                </button>
            </div>

            <div class="col-md-6 col-lg-3">
                <button type="button" class="btn btn-outline-success w-100 py-3 system-action-btn"
                    data-url="{{ route('admin.settings.configCache') }}"
                    data-confirm-title="Cache Configuration?"
                    data-confirm-text="This will cache the current config files."
                    data-confirm-icon="question"
                    data-confirm-button="Yes, Cache"
                    data-confirm-button-class="btn btn-primary"
                    data-confirm-cancel-button-class="btn btn-secondary">
                    <i class="bi bi-lightning-charge fs-4 d-block mb-1"></i>
                    <strong>Config Cache</strong>
                    <div class="small text-muted mt-1">Speed up boot time</div>
                </button>
            </div>

            <div class="col-md-6 col-lg-3">
                <button type="button" class="btn btn-outline-warning w-100 py-3 system-action-btn"
                    data-url="{{ route('admin.settings.migrate') }}"
                    data-confirm-title="Run Migrations?"
                    data-confirm-text="This will run pending database migrations."
                    data-confirm-icon="question"
                    data-confirm-button="Yes, Migrate"
                    data-confirm-button-class="btn btn-warning"
                    data-confirm-cancel-button-class="btn btn-secondary">
                    <i class="bi bi-database-check fs-4 d-block mb-1"></i>
                    <strong>Run Migrate</strong>
                    <div class="small text-muted mt-1">Execute pending schemas</div>
                </button>
            </div>

            <div class="col-md-6 col-lg-3">
                <button type="button" class="btn btn-outline-danger w-100 py-3 system-action-btn"
                    data-url="{{ route('admin.settings.migrateFreshSeed') }}"
                    data-confirm-title="Run Migrate Fresh + Seed?"
                    data-confirm-text="⚠ This will DROP ALL TABLES, re-run migrations, and seed the database with default data. This cannot be undone!"
                    data-confirm-icon="warning"
                    data-confirm-button="Yes, I understand"
                    data-confirm-button-class="btn btn-danger"
                    data-confirm-cancel-button-class="btn btn-secondary">
                    <i class="bi bi-exclamation-triangle fs-4 d-block mb-1"></i>
                    <strong>Fresh & Seed</strong>
                    <div class="small text-muted mt-1">Reset & Seed defaults</div>
                </button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    {!! \App\Helpers\UtilityHelper::returnScriptWithNonce(asset('assets/js/backend/general-settings.js')) !!}
@endpush
