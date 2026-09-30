document.addEventListener('DOMContentLoaded', function () {

    const otpForm = document.getElementById('otp-form');

    if (!otpForm) {
        return;
    }

    const inputs = otpForm.querySelectorAll('.otp-input');

    const verifyButton =
        document.getElementById('verify-otp-btn');

    const countdown =
        document.getElementById('otp-countdown');

    const attemptInfo =
        document.getElementById('attempt-info');

    const resendContainer =
        document.getElementById('resend-container');

    /*
    |--------------------------------------------------------------------------
    | Values from Blade data attributes
    |--------------------------------------------------------------------------
    */

    const expiresAt =
        otpForm.dataset.expiresAt;

    const currentAttempts =
        parseInt(
            otpForm.dataset.attempts || '0',
            10
        );

    const maxAttempts =
        parseInt(
            otpForm.dataset.maxAttempts || '3',
            10
        );

    /*
    |--------------------------------------------------------------------------
    | Disable OTP Form
    |--------------------------------------------------------------------------
    */

    function disableOtpForm() {

        inputs.forEach(function (input) {
            input.disabled = true;
        });

        if (verifyButton) {
            verifyButton.disabled = true;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Enable OTP Form
    |--------------------------------------------------------------------------
    */

    function enableOtpForm() {

        inputs.forEach(function (input) {
            input.disabled = false;
        });

        if (verifyButton) {
            verifyButton.disabled = false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Show Resend OTP
    |--------------------------------------------------------------------------
    */

    function showResendOtp() {

        if (resendContainer) {
            resendContainer.classList.remove('d-none');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Hide Resend OTP
    |--------------------------------------------------------------------------
    */

    function hideResendOtp() {

        if (resendContainer) {
            resendContainer.classList.add('d-none');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Initial Attempt State
    |--------------------------------------------------------------------------
    */

    if (currentAttempts >= maxAttempts) {

        disableOtpForm();

        showResendOtp();

        if (attemptInfo) {
            attemptInfo.textContent =
                'Maximum attempts reached. Please resend OTP.';

            attemptInfo.classList.remove(
                'text-warning'
            );

            attemptInfo.classList.add(
                'text-danger'
            );
        }

    } else {

        enableOtpForm();

        hideResendOtp();

        if (attemptInfo) {

            const remaining =
                maxAttempts - currentAttempts;

            attemptInfo.textContent =
                remaining +
                ' attempt' +
                (remaining === 1 ? '' : 's') +
                ' remaining';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | OTP Input
    |--------------------------------------------------------------------------
    */

    inputs.forEach(function (input, index) {

        input.addEventListener('input', function () {

            this.value =
                this.value.replace(/[^0-9]/g, '');

            if (
                this.value &&
                index < inputs.length - 1
            ) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', function (event) {

            if (
                event.key === 'Backspace' &&
                !this.value &&
                index > 0
            ) {
                inputs[index - 1].focus();
            }
        });

    });

    /*
    |--------------------------------------------------------------------------
    | Paste OTP
    |--------------------------------------------------------------------------
    */

    if (inputs.length > 0) {

        inputs[0].addEventListener(
            'paste',
            function (event) {

                event.preventDefault();

                const pastedOtp =
                    event.clipboardData
                        .getData('text')
                        .replace(/\D/g, '')
                        .substring(0, inputs.length);

                pastedOtp
                    .split('')
                    .forEach(function (digit, index) {

                        if (inputs[index]) {
                            inputs[index].value = digit;
                        }

                    });

                if (pastedOtp.length > 0) {

                    const focusIndex =
                        Math.min(
                            pastedOtp.length - 1,
                            inputs.length - 1
                        );

                    inputs[focusIndex].focus();
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Countdown
    |--------------------------------------------------------------------------
    */

    if (countdown && expiresAt) {

        const expiryTime =
            new Date(expiresAt).getTime();

        function updateCountdown() {

            const now =
                new Date().getTime();

            const distance =
                expiryTime - now;

            if (distance <= 0) {

                countdown.textContent =
                    'OTP Expired';

                countdown.classList.add(
                    'expired'
                );

                disableOtpForm();

                showResendOtp();

                if (attemptInfo) {

                    attemptInfo.textContent =
                        'OTP expired. Please resend OTP.';

                    attemptInfo.classList.remove(
                        'text-warning'
                    );

                    attemptInfo.classList.add(
                        'text-danger'
                    );
                }

                return false;
            }

            const minutes =
                Math.floor(
                    distance / (1000 * 60)
                );

            const seconds =
                Math.floor(
                    (distance % (1000 * 60)) / 1000
                );

            countdown.textContent =
                String(minutes).padStart(2, '0') +
                ':' +
                String(seconds).padStart(2, '0');

            return true;
        }

        updateCountdown();

        const countdownInterval =
            setInterval(function () {

                const active =
                    updateCountdown();

                if (!active) {
                    clearInterval(
                        countdownInterval
                    );
                }

            }, 1000);
    }

    /*
    |--------------------------------------------------------------------------
    | Focus First Input
    |--------------------------------------------------------------------------
    */

    if (
        inputs.length > 0 &&
        currentAttempts < maxAttempts
    ) {
        inputs[0].focus();
    }

});
