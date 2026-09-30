function initOtpSettings() {
    const radioTrue = document.getElementById('otp_is_default_true');
    const radioFalse = document.getElementById('otp_is_default_false');
    const otpLengthSelect = document.getElementById('otp_length');
    const otpDefaultInput = document.getElementById('otp_default');
    const otpDefaultHelp = document.getElementById('otp_default_help');
    const otpForm = document.getElementById('otp-settings-form');
    const otpBtn = document.getElementById('otp-settings-btn');

    function clearValidationErrors(form) {
        if (!form) return;
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback.dynamic-error').forEach(el => el.remove());
    }

    function showValidationErrors(form, errors) {
        clearValidationErrors(form);
        if (!form || !errors) return;

        for (const [field, messages] of Object.entries(errors)) {
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

    if (otpLengthSelect && otpDefaultInput) {
        function toggleDefaultOtpInput() {
            if (radioTrue && radioTrue.checked) {
                otpDefaultInput.disabled = false;
                otpDefaultInput.classList.remove('bg-body-secondary');
                if (otpDefaultHelp) {
                    otpDefaultHelp.textContent = 'Static OTP code used when is_default is set to true.';
                    otpDefaultHelp.className = 'text-muted mt-1 d-block small';
                }
            } else if (radioFalse && radioFalse.checked) {
                otpDefaultInput.disabled = true;
                otpDefaultInput.classList.add('bg-body-secondary');
                if (otpDefaultHelp) {
                    otpDefaultHelp.textContent = 'Static OTP code used when is_default is set to true.';
                    otpDefaultHelp.className = 'text-muted mt-1 d-block small';
                }
            }
        }

        function updateOtpLengthConstraint(resetValue = true) {
            const targetLength = parseInt(otpLengthSelect.value, 10) || 4;
            otpDefaultInput.setAttribute('maxlength', targetLength);
            otpDefaultInput.setAttribute('placeholder', 'e.g. ' + '9'.repeat(targetLength));

            if (resetValue) {
                otpDefaultInput.value = '9'.repeat(targetLength);
            }
        }

        if (radioTrue) radioTrue.addEventListener('change', toggleDefaultOtpInput);
        if (radioFalse) radioFalse.addEventListener('change', toggleDefaultOtpInput);

        otpLengthSelect.addEventListener('change', function () {
            updateOtpLengthConstraint(true);
        });

        otpDefaultInput.addEventListener('input', function () {
            const targetLength = parseInt(otpLengthSelect.value, 10) || 4;
            let val = this.value.replace(/\D/g, '');
            if (val.length > targetLength) {
                val = val.substring(0, targetLength);
            }
            this.value = val;
        });

        toggleDefaultOtpInput();
        updateOtpLengthConstraint(false);
    }

    if (otpForm && otpBtn) {
        otpForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            clearValidationErrors(otpForm);

            const formData = new FormData(otpForm);
            const originalBtnHtml = otpBtn.innerHTML;

            otpBtn.disabled = true;
            otpBtn.innerHTML = `
                ${originalBtnHtml}
                <span
                    class="spinner-border spinner-border-sm ms-1"
                    role="status"
                    aria-hidden="true"
                ></span>`;

            try {
                const response = await fetch(otpForm.action, {
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
                    showValidationErrors(otpForm, data.errors);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Please correct the highlighted errors.');
                    }
                    return;
                }

                if (response.ok && data.status) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message || 'OTP settings saved successfully.');
                    } else {
                        alert(data.message || 'OTP settings saved successfully.');
                    }
                    return;
                }

                const msg = data.message || 'Failed to save settings.';
                if (typeof toastr !== 'undefined') {
                    toastr.error(msg);
                } else {
                    alert(msg);
                }
            } catch (err) {
                console.error(err);
                if (typeof toastr !== 'undefined') {
                    toastr.error('An error occurred while saving OTP settings.');
                } else {
                    alert('An error occurred while saving OTP settings.');
                }
            } finally {
                otpBtn.disabled = false;
                otpBtn.innerHTML = originalBtnHtml;
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initOtpSettings);
} else {
    initOtpSettings();
}
