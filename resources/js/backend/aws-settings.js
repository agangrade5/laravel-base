function initAwsSettings() {
    const awsForm = document.getElementById('aws-settings-form');
    const awsBtn = document.getElementById('aws-settings-btn');

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

    if (!awsForm || !awsBtn) return;

    awsForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearValidationErrors(awsForm);

        const formData = new FormData(awsForm);
        const originalBtnHtml = awsBtn.innerHTML;

        awsBtn.disabled = true;
        awsBtn.innerHTML = `
            ${originalBtnHtml}
            <span
                class="spinner-border spinner-border-sm ms-1"
                role="status"
                aria-hidden="true"
            ></span>`;

        try {
            const response = await fetch(awsForm.action, {
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
                showValidationErrors(awsForm, data.errors);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Please correct the highlighted errors.');
                }
                return;
            }

            if (response.ok && data.status) {
                if (typeof toastr !== 'undefined') {
                    toastr.success(data.message || 'AWS settings saved successfully.');
                } else {
                    alert(data.message || 'AWS settings saved successfully.');
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
                toastr.error('An error occurred while saving AWS settings.');
            } else {
                alert('An error occurred while saving AWS settings.');
            }
        } finally {
            awsBtn.disabled = false;
            awsBtn.innerHTML = originalBtnHtml;
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAwsSettings);
} else {
    initAwsSettings();
}
