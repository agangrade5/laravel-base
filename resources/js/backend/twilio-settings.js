function initTwilioSettings() {
    const enableTwilioToggle = document.getElementById('enable_twilio');
    const twilioAccountSid = document.getElementById('twilio_account_sid');
    const twilioAuthToken = document.getElementById('twilio_auth_token');
    const twilioFromNumber = document.getElementById('twilio_from_number');
    const twilioForm = document.getElementById('twilio-settings-form');
    const twilioBtn = document.getElementById('twilio-settings-btn');

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

    if (enableTwilioToggle) {
        const fields = [twilioAccountSid, twilioAuthToken, twilioFromNumber].filter(Boolean);

        function toggleTwilioInputs() {
            const isEnabled = enableTwilioToggle.checked;
            fields.forEach(field => {
                field.disabled = !isEnabled;
                if (!isEnabled) {
                    field.classList.add('bg-body-secondary');
                } else {
                    field.classList.remove('bg-body-secondary');
                }
            });
        }

        enableTwilioToggle.addEventListener('change', toggleTwilioInputs);

        // Initial trigger on load
        toggleTwilioInputs();
    }

    if (twilioForm && twilioBtn) {
        twilioForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            clearValidationErrors(twilioForm);

            const formData = new FormData(twilioForm);
            const originalBtnHtml = twilioBtn.innerHTML;

            twilioBtn.disabled = true;
            twilioBtn.innerHTML = `
                ${originalBtnHtml}
                <span
                    class="spinner-border spinner-border-sm ms-1"
                    role="status"
                    aria-hidden="true"
                ></span>`;

            try {
                const response = await fetch(twilioForm.action, {
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
                    showValidationErrors(twilioForm, data.errors);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Please correct the highlighted errors.');
                    }
                    return;
                }

                if (response.ok && data.status) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message || 'Twilio settings saved successfully.');
                    } else {
                        alert(data.message || 'Twilio settings saved successfully.');
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
                    toastr.error('An error occurred while saving Twilio settings.');
                } else {
                    alert('An error occurred while saving Twilio settings.');
                }
            } finally {
                twilioBtn.disabled = false;
                twilioBtn.innerHTML = originalBtnHtml;
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTwilioSettings);
} else {
    initTwilioSettings();
}
