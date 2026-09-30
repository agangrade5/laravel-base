document.addEventListener('DOMContentLoaded', () => {
    // 1. Interactive user search helper
    const userSearchInput = document.getElementById('user-search');
    if (userSearchInput) {
        if (userSearchInput.value) {
            userSearchInput.focus();
            const val = userSearchInput.value;
            userSearchInput.value = '';
            userSearchInput.value = val;
        }

        userSearchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('table tbody tr');

            rows.forEach((row) => {
                if (row.querySelector('.py-5')) return;

                const text = row.textContent.toLowerCase();
                if (!query || text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // 2. Auto-open modal if validation errors exist (detected via .is-invalid inside modal)
    const editModalEl = document.getElementById('editUserModal');
    const addModalEl = document.getElementById('addUserModal');

    if (editModalEl && editModalEl.querySelector('.is-invalid')) {
        const editUserId = document.getElementById('edit_user_id')?.value;
        if (editUserId) {
            const form = document.getElementById('edit-user-form');
            if (form) {
                form.action = `/admin/users/update/${editUserId}`;
            }
        }
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const editUserModal = new bootstrap.Modal(editModalEl);
            editUserModal.show();
        }
    } else if (addModalEl && addModalEl.querySelector('.is-invalid')) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const addUserModal = new bootstrap.Modal(addModalEl);
            addUserModal.show();
        }
    }

    // 3. Edit user button modal handler
    document.querySelectorAll('.edit-user-btn').forEach((button) => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const email = this.dataset.email;
            const phone = this.dataset.phone;
            const countryCode = this.dataset.countryCode || '+91';
            const active = this.dataset.active;

            const form = document.getElementById('edit-user-form');
            if (form) {
                form.action = `/admin/users/update/${id}`;
            }

            const editIdInput = document.getElementById('edit_user_id');
            const editNameInput = document.getElementById('edit_user_name');
            const editEmailInput = document.getElementById('edit_user_email');
            const editPhoneInput = document.getElementById('edit_user_phone');
            const editStatusInput = document.getElementById('edit_user_status');
            const editPassInput = document.getElementById('edit_user_password');

            if (editIdInput) editIdInput.value = id;
            if (editNameInput) editNameInput.value = name;
            if (editEmailInput) editEmailInput.value = email;
            if (editPhoneInput) editPhoneInput.value = phone || '';

            const editInstance = window.countryDropdownInstances['edit_countryDropdownWrapper'];
            if (editInstance) {
                const matchingOption = editInstance.dropdown.querySelector(`.country-option[data-code="${countryCode}"]`);
                const iso = matchingOption?.dataset.iso;
                const countryName = matchingOption?.dataset.name;
                editInstance.setValue(countryCode, iso, countryName);
            }


            if (editStatusInput) editStatusInput.value = active;
            if (editPassInput) editPassInput.value = '';

            if (editModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const editModal = new bootstrap.Modal(editModalEl);
                editModal.show();
            }
        });
    });

    // 4. AJAX Form Submission for Add & Edit User Modals
    function handleUserFormSubmit(formId, modalId) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            // Clear previous errors
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback.dynamic-error').forEach(el => el.remove());

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Processing...
                `;
            }

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': formData.get('_token') || ''
                    }
                });

                const data = await response.json();

                if (response.status === 422) {
                    // Show inline validation errors
                    if (data.errors) {
                        for (const [field, messages] of Object.entries(data.errors)) {
                            const input = form.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'invalid-feedback dynamic-error d-block mt-1';
                                errorDiv.textContent = Array.isArray(messages) ? messages[0] : messages;
                                const inputGroup = input.closest('.input-group');
                                if (inputGroup) {
                                    inputGroup.after(errorDiv);
                                } else {
                                    input.after(errorDiv);
                                }
                            }
                        }
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Please correct the highlighted errors.');
                    }
                    return;
                }

                if (response.ok && data.status) {
                    // Hide modal
                    const modalEl = document.getElementById(modalId);
                    if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modalInstance.hide();
                    }

                    // Reload page cleanly - session flash will show single toast notification
                    window.location.reload();
                    return;
                }

                const msg = data.message || 'An error occurred while saving user.';
                if (typeof toastr !== 'undefined') {
                    toastr.error(msg);
                } else {
                    alert(msg);
                }
            } catch (err) {
                console.error(err);
                if (typeof toastr !== 'undefined') {
                    toastr.error('An unexpected error occurred.');
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }
        });
    }

    handleUserFormSubmit('add-user-form', 'addUserModal');
    handleUserFormSubmit('edit-user-form', 'editUserModal');
});
