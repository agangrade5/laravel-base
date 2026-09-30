<div class="card settings-card mb-4">
    <div class="card-header d-flex align-items-center">
        <i class="bi bi-person-fill fs-4 me-2 text-primary"></i>
        <div>
            <h5 class="mb-0 fw-bold">Account Information</h5>
            <small class="text-muted">Update your profile details and preferences</small>
        </div>
    </div>
    <div class="card-body">
        <!-- Profile Avatar Section -->
        <div class="d-flex flex-column flex-sm-row align-items-center gap-4 mb-5 pb-4 border-bottom">
            <div class="avatar-preview-wrapper">
                <img
                    src="{{ $user->image
                        ? Storage::disk(config('filesystems.default'))->url($user->image)
                        : asset('assets/images/backend/user2-160x160.jpg') }}"
                    alt="{{ $user->name }}"
                    class="avatar-preview-img"
                    id="avatar-preview"
                >
                <label
                    for="avatar-file-input"
                    class="avatar-upload-overlay"
                >
                    <i class="bi bi-camera"></i>
                </label>
            </div>
            <div class="text-center text-sm-start">
                <h6 class="mb-1 fw-bold">Profile Picture</h6>
                <p class="text-muted small mb-3">JPG, JPEG, PNG, or WEBP. Max size 2MB.</p>
                <div class="d-flex gap-2 justify-content-center justify-content-sm-start">
                    <label for="avatar-file-input" class="btn btn-outline-primary btn-sm px-3">
                        Upload Photo
                    </label>
                    {{-- <button type="button" class="btn btn-outline-secondary btn-sm" id="remove-avatar-btn">
                        Remove
                    </button> --}}
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <form
            method="POST"
            action="{{ $isAdmin ? route('admin.settings.profile.update') : route('settings.profile.update') }}"
            id="account-settings-form"
            enctype="multipart/form-data"
            class="row g-4"
        >
            @csrf

            <input
                type="file"
                name="profile_image"
                id="avatar-file-input"
                class="d-none"
                accept="image/jpg,image/jpeg,image/png,image/webp"
            >
            <input
                type="hidden"
                name="remove_profile_image"
                id="remove-profile-image"
                value="0"
            >
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="name">Full Name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input
                        type="text"
                        class="form-control"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        placeholder="Enter full name"
                        required
                    >
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="email">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        value="{{ $user->email }}"
                        disabled
                    >
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small" for="phone_number">Phone number</label>
                <div class="input-group">
                    @include('partials.country-code-dropdown', [
                        'idPrefix'     => '',
                        'selectedCode' => old('country_code', $user->country_code ?? '+91'),
                    ])

                    <input
                        type="tel"
                        class="form-control rounded-start-0 @error('phone_number') is-invalid @enderror"
                        id="phone_number"
                        name="phone_number"
                        value="{{ old('phone_number', $user->phone_number) }}"
                        placeholder="Enter mobile number"
                        inputmode="numeric"
                        maxlength="15"
                        required
                    >
                    @error('phone_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                @error('country_code')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary px-4 py-2" id="save-account-btn">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
    {!! \App\Helpers\UtilityHelper::returnScriptWithNonce(asset('assets/js/backend/account-settings.js')) !!}
@endpush
