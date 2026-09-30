<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true"
     data-bs-backdrop="static"
     data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content settings-card border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="editUserModalLabel">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Edit User Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.users.update', old('edit_user_id', 0)) }}" method="POST" id="edit-user-form" enctype="multipart/form-data" class="row g-4 needs-validation" novalidate>
                @csrf
                <input type="hidden" name="form_type" value="edit">
                <input type="hidden" name="edit_user_id" id="edit_user_id" value="{{ old('edit_user_id') }}">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small" for="edit_user_name">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="name" id="edit_user_name" class="form-control @if(old('form_type') === 'edit') @error('name') is-invalid @enderror @endif" value="{{ old('form_type') === 'edit' ? old('name') : '' }}" placeholder="Enter full name" required>
                            @if(old('form_type') === 'edit')
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small" for="edit_user_email">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" id="edit_user_email" class="form-control bg-body-secondary @if(old('form_type') === 'edit') @error('email') is-invalid @enderror @endif" value="{{ old('form_type') === 'edit' ? old('email') : '' }}" placeholder="name@example.com" readonly>
                            @if(old('form_type') === 'edit')
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small" for="edit_user_phone">
                            Mobile Number
                        </label>

                        <div class="input-group">
                            @include('partials.country-code-dropdown', [
                                'idPrefix'     => 'edit_',
                                'selectedCode' => old('country_code', '+91'),
                            ])

                            <input
                                type="tel"
                                name="phone_number"
                                id="edit_user_phone"
                                class="form-control rounded-start-0 @if(old('form_type') === 'edit') @error('phone_number') is-invalid @enderror @endif"
                                value="{{ old('form_type') === 'edit' ? old('phone_number') : '' }}"
                                placeholder="Enter mobile number"
                                inputmode="numeric"
                                maxlength="15"
                                required
                            >

                            @if(old('form_type') === 'edit')
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>


                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small" for="edit_user_status">Status</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-toggle-on"></i></span>
                            <select name="is_active" id="edit_user_status" class="form-select" required>
                                <option value="1" {{ old('form_type') === 'edit' && old('is_active') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('form_type') === 'edit' && old('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <!-- <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small" for="edit_user_password">New Password (Optional)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="edit_user_password" class="form-control @if(old('form_type') === 'edit') @error('password') is-invalid @enderror @endif" placeholder="Leave blank to keep current">
                            @if(old('form_type') === 'edit')
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div> -->

                </div>
                <div class="modal-footer border-top-0 p-3 bg-light-subtle">
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
