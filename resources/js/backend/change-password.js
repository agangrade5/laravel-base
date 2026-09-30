document.addEventListener('DOMContentLoaded', () => {

    const resetStrengthMeter = () => {
        const bars = [
            document.getElementById('bar-1'),
            document.getElementById('bar-2'),
            document.getElementById('bar-3'),
            document.getElementById('bar-4')
        ];
        bars.forEach(bar => {
            bar.className = 'flex-grow-1 strength-meter-bar bg-secondary-subtle';
        });
        const strengthText = document.getElementById('strength-text');
        strengthText.textContent = "Password should be 8-15 characters & include 1 uppercase, a lowercase, a special character & a number.";
        strengthText.className = "text-muted";
    }

    // Password strength indicator logic
    const pwdNew = document.getElementById('pwd-new');
    const bars = [
        document.getElementById('bar-1'),
        document.getElementById('bar-2'),
        document.getElementById('bar-3'),
        document.getElementById('bar-4')
    ];
    const strengthText = document.getElementById('strength-text');

    if (pwdNew) {
        pwdNew.addEventListener('input', (e) => {
            const val = e.target.value;
            let score = 0;
            if (val.length >= 8 && val.length <= 15) score++;
            if (/[a-z]/.test(val)) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[#?!@$%^&*\-]/.test(val)) score++;

            bars.forEach(bar => {
                bar.className = 'flex-grow-1 strength-meter-bar bg-secondary-subtle';
            });

            if (val.length === 0) {
                resetStrengthMeter();
                return;
            }

            if (score <= 2) {
                bars[0].className = 'flex-grow-1 strength-meter-bar bg-danger';
                strengthText.textContent = "Weak password";
                strengthText.className = "text-danger small";
            } else if (score === 3) {
                bars[0].className = 'flex-grow-1 strength-meter-bar bg-warning';
                bars[1].className = 'flex-grow-1 strength-meter-bar bg-warning';
                strengthText.textContent = "Fair password";
                strengthText.className = "text-warning small";
            } else if (score === 4) {
                bars[0].className = 'flex-grow-1 strength-meter-bar bg-info';
                bars[1].className = 'flex-grow-1 strength-meter-bar bg-info';
                bars[2].className = 'flex-grow-1 strength-meter-bar bg-info';
                strengthText.textContent = "Strong password";
                strengthText.className = "text-info small";
            } else if (score === 5) {
                bars.forEach(bar => bar.className = 'flex-grow-1 strength-meter-bar bg-success');
                strengthText.textContent = "Very secure password";
                strengthText.className = "text-success small";
            }
        });
    }

    /* Change Password Js */

    const form = document.getElementById('change-password-form');

    if (!form) {
        return;
    }

    const button = document.getElementById('change-password-btn');

    const currentPassword = document.getElementById('pwd-current');
    const password = document.getElementById('pwd-new');
    const passwordConfirmation = document.getElementById('pwd-confirm');

    const currentPasswordError =
        document.getElementById('current-password-error');

    const passwordError =
        document.getElementById('password-error');

    const passwordConfirmationError =
        document.getElementById('password-confirmation-error');


    /*
    |--------------------------------------------------------------------------
    | Clear Validation
    |--------------------------------------------------------------------------
    */

    const clearValidation = () => {

        [
            currentPassword,
            password,
            passwordConfirmation
        ].forEach(input => {
            input.classList.remove('is-invalid');
        });

        [
            currentPasswordError,
            passwordError,
            passwordConfirmationError
        ].forEach(error => {
            error.textContent = '';
        });
    };


    /*
    |--------------------------------------------------------------------------
    | Show Validation Errors
    |--------------------------------------------------------------------------
    */

    const showValidationErrors = (errors) => {
        if (errors.current_password) {

            currentPassword.classList.add('is-invalid');

            currentPasswordError.textContent =
                errors.current_password[0];
        }

        if (errors.password) {

            password.classList.add('is-invalid');

            passwordError.textContent =
                errors.password[0];
        }

        if (errors.password_confirmation) {

            passwordConfirmation.classList.add('is-invalid');

            passwordConfirmationError.textContent =
                errors.password_confirmation[0];
        }
    };


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    form.addEventListener('submit', async (event) => {

        event.preventDefault();

        clearValidation();

        const formData = new FormData(form);

        const originalButtonHtml = button.innerHTML;

        button.disabled = true;

        button.innerHTML = `
            ${originalButtonHtml}
            <span
                class="spinner-border spinner-border-sm me-1"
                role="status"
                aria-hidden="true"
            ></span>`;

        try {

            const response = await fetch(
                form.action,
                {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': form.querySelector(
                            'input[name="_token"]'
                        ).value,

                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );


            const data = await response.json();


            /*
            |--------------------------------------------------------------------------
            | Validation Error
            |--------------------------------------------------------------------------
            */

            if (response.status === 422) {

                showValidationErrors(data.errors);

                toastr.error(
                    'Please correct the highlighted errors.'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            if (response.ok && data.status) {

                form.reset();

                currentPassword.focus();
                resetStrengthMeter();

                toastr.success(data.message);

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Other Error
            |--------------------------------------------------------------------------
            */

            toastr.error(
                data.message ||
                'Something went wrong. Please try again.'
            );

        } catch (error) {

            console.error(error);

            toastr.error(
                'Unable to update password. Please try again.'
            );

        } finally {

            button.disabled = false;

            button.innerHTML = originalButtonHtml;
        }
    });

});
