document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */

    const startBtn =
        document.getElementById('tfa-start-setup');

    const setupModalElement =
        document.getElementById('tfa-setup-modal');

    const setupLoading =
        document.getElementById('tfa-setup-loading');

    const setupContent =
        document.getElementById('tfa-setup-content');

    const qrHolder =
        document.getElementById('tfa-qr-holder');

    const secretText =
        document.getElementById('tfa-secret-text');

    const confirmBtn =
        document.getElementById('tfa-confirm-enable');

    const confirmCode =
        document.getElementById('tfa-confirm-code');

    const copySecretBtn =
        document.getElementById('tfa-copy-secret');

    const copyMessage =
        document.getElementById('tfa-copy-message');

    const disableForm =
        document.getElementById('tfa-disable-form');

    const disableBtn = disableForm
        ? disableForm.querySelector('button[type="submit"]')
        : null;

    const enableBlock =
        document.getElementById('tfa-enable-block');

    const disableBlock =
        document.getElementById('tfa-disable-block');

    const statusBadge =
        document.getElementById('tfa-status-badge');

    /*
    |--------------------------------------------------------------------------
    | Bootstrap Modal
    |--------------------------------------------------------------------------
    */

    let setupModal = null;

    if (
        setupModalElement &&
        typeof bootstrap !== 'undefined'
    ) {
        setupModal =
            bootstrap.Modal.getOrCreateInstance(
                setupModalElement
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Nothing to initialize
    |--------------------------------------------------------------------------
    */

    if (!startBtn && !disableForm) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | CSRF Token
    |--------------------------------------------------------------------------
    */

    const csrfMeta =
        document.querySelector(
            'meta[name="csrf-token"]'
        );

    const csrfToken = csrfMeta
        ? csrfMeta.getAttribute('content')
        : '';

    /*
    |--------------------------------------------------------------------------
    | Show Toastr Alert
    |--------------------------------------------------------------------------
    */

    function showAlert(type, message) {

        if (typeof toastr === 'undefined') {
            alert(message);
            return;
        }

        if (type === 'success') {
            toastr.success(message);
        } else {
            toastr.error(message);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Parse Fetch Response
    |--------------------------------------------------------------------------
    */

    async function parseResponse(response) {

        const contentType =
            response.headers.get('content-type') || '';

        if (
            contentType.includes(
                'application/json'
            )
        ) {
            return await response.json();
        }

        return {
            success: false,
            message:
                'Unexpected server response. Please try again.'
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Reset Setup Modal
    |--------------------------------------------------------------------------
    */

    function resetSetupModal() {

        if (qrHolder) {
            qrHolder.innerHTML = '';
        }

        if (secretText) {
            secretText.value = '';
        }

        if (confirmCode) {
            confirmCode.value = '';
            confirmCode.classList.remove(
                'is-invalid'
            );
        }

        if (copyMessage) {
            copyMessage.style.display = 'none';
        }

        if (copySecretBtn) {
            copySecretBtn.disabled = false;

            copySecretBtn.innerHTML =
                '<i class="bi bi-clipboard"></i>';
        }

        if (setupLoading) {
            setupLoading.style.display = 'none';
        }

        if (setupContent) {
            setupContent.style.display = 'block';
        }

        /*
        |--------------------------------------------------------------------------
        | Restore Confirm Button
        |--------------------------------------------------------------------------
        */

        if (confirmBtn) {

            confirmBtn.disabled = false;

            confirmBtn.innerHTML = `
                <i class="bi bi-shield-check me-1"></i>
                Confirm &amp; Enable 2FA
            `;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Start Google 2FA Setup
    |--------------------------------------------------------------------------
    */

    if (startBtn) {

        startBtn.addEventListener(
            'click',
            async function () {

                if (startBtn.disabled) {
                    return;
                }

                const originalText =
                    startBtn.innerHTML;

                /*
                |--------------------------------------------------------------------------
                | Disable Main Button
                |--------------------------------------------------------------------------
                */

                startBtn.disabled = true;

                startBtn.innerHTML = `
                    <span
                        class="spinner-border spinner-border-sm me-1"
                        role="status"
                        aria-hidden="true"
                    ></span>
                    Preparing...
                `;


                /*
                |--------------------------------------------------------------------------
                | Reset Previous Setup
                |--------------------------------------------------------------------------
                */

                resetSetupModal();


                /*
                |--------------------------------------------------------------------------
                | Show Loading Inside Modal
                |--------------------------------------------------------------------------
                */

                if (setupLoading) {
                    setupLoading.style.display = 'block';
                }

                if (setupContent) {
                    setupContent.style.display = 'none';
                }

                /*
                |--------------------------------------------------------------------------
                | Open Modal
                |--------------------------------------------------------------------------
                */

                if (setupModal) {
                    setupModal.show();
                }

                try {

                    const response = await fetch(
                        route('admin.settings.twoFa.setup'),
                        {
                            method: 'GET',
                            headers: {
                                'X-CSRF-TOKEN':
                                    csrfToken,
                                'Accept':
                                    'application/json',
                            },
                        }
                    );

                    const data =
                        await parseResponse(response);

                    /*
                    |--------------------------------------------------------------------------
                    | API Error
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !response.ok ||
                        !data.success
                    ) {

                        showAlert(
                            'danger',
                            data.message ||
                            'Could not start 2FA setup.'
                        );

                        if (setupModal) {
                            setupModal.hide();
                        }

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | QR Code
                    |--------------------------------------------------------------------------
                    */

                    if (qrHolder) {
                        qrHolder.innerHTML =
                            data.qr_code || '';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Secret Key
                    |--------------------------------------------------------------------------
                    |
                    | secretText is an INPUT element,
                    | therefore use .value instead of
                    | .textContent.
                    |
                    */

                    if (secretText) {
                        secretText.value =
                            data.secret || '';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Show Setup Content
                    |--------------------------------------------------------------------------
                    */

                    if (setupLoading) {
                        setupLoading.style.display =
                            'none';
                    }

                    if (setupContent) {
                        setupContent.style.display =
                            'block';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Focus OTP
                    |--------------------------------------------------------------------------
                    */

                    setTimeout(function () {
                        if (confirmCode) {
                            confirmCode.focus();
                        }
                    }, 300);

                } catch (error) {
                    console.error(
                        '2FA setup error:',
                        error
                    );
                    showAlert(
                        'danger',
                        'Something went wrong. Please try again.'
                    );
                    if (setupModal) {
                        setupModal.hide();
                    }
                } finally {
                    /*
                    |--------------------------------------------------------------------------
                    | Restore Main Button
                    |--------------------------------------------------------------------------
                    */
                    startBtn.disabled = false;
                    startBtn.innerHTML =
                        originalText;
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Confirm & Enable Google 2FA
    |--------------------------------------------------------------------------
    */

    if (confirmBtn) {

        confirmBtn.addEventListener(
            'click',
            async function () {
                const otp = confirmCode
                    ? confirmCode.value.trim()
                    : '';
                /*
                |--------------------------------------------------------------------------
                | Validate OTP
                |--------------------------------------------------------------------------
                */
                if (!/^\d{6}$/.test(otp)) {
                    showAlert(
                        'danger',
                        'Please enter a valid 6-digit authentication code.'
                    );
                    if (confirmCode) {
                        confirmCode.classList.add(
                            'is-invalid'
                        );
                        confirmCode.focus();
                    }
                    return;
                }

                if (confirmCode) {
                    confirmCode.classList.remove(
                        'is-invalid'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Prevent Multiple Requests
                |--------------------------------------------------------------------------
                */

                if (confirmBtn.disabled) {
                    return;
                }
                const originalText =
                    confirmBtn.innerHTML;
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = `
                    <span
                        class="spinner-border spinner-border-sm me-1"
                        role="status"
                        aria-hidden="true"
                    ></span>
                    Verifying...
                `;

                try {
                    const response = await fetch(
                        route('admin.settings.twoFa.enable'),
                        {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN':
                                    csrfToken,
                                'Content-Type':
                                    'application/json',
                                'Accept':
                                    'application/json',
                            },
                            body: JSON.stringify({
                                one_time_password:
                                    otp
                            }),
                        }
                    );

                    const data =
                        await parseResponse(response);
                    /*
                    |--------------------------------------------------------------------------
                    | Invalid OTP / API Error
                    |--------------------------------------------------------------------------
                    |
                    | Modal stays OPEN.
                    |
                    */

                    if (
                        !response.ok ||
                        !data.success
                    ) {
                        showAlert(
                            'danger',
                            data.message ||
                            'Invalid authentication code.'
                        );
                        if (confirmCode) {
                            confirmCode.value = '';
                            confirmCode.focus();
                        }

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Success
                    |--------------------------------------------------------------------------
                    */

                    showAlert(
                        'success',
                        data.message ||
                        'Google 2FA enabled successfully.'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Update Status Badge
                    |--------------------------------------------------------------------------
                    */

                    if (statusBadge) {
                        statusBadge.innerHTML = `
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>
                                Enabled
                            </span>
                        `;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Hide Enable Block
                    |--------------------------------------------------------------------------
                    */
                    if (enableBlock) {
                        enableBlock.style.display =
                            'none';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Show Disable Block
                    |--------------------------------------------------------------------------
                    */
                    if (disableBlock) {
                        disableBlock.style.display =
                            'block';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Close Modal
                    |--------------------------------------------------------------------------
                    */
                    if (setupModal) {
                        setupModal.hide();
                    }

                } catch (error) {
                    console.error(
                        '2FA enable error:',
                        error
                    );
                    showAlert(
                        'danger',
                        'Something went wrong. Please try again.'
                    );
                } finally {
                    /*
                    |--------------------------------------------------------------------------
                    | Restore Confirm Button
                    |--------------------------------------------------------------------------
                    */
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML =
                        originalText;
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Copy Secret Key
    |--------------------------------------------------------------------------
    */

    if (copySecretBtn) {
        copySecretBtn.addEventListener(
            'click',
            async function () {
                const secret = secretText
                    ? secretText.value.trim()
                    : '';
                if (!secret) {
                    showAlert(
                        'danger',
                        'No secret key available.'
                    );
                    return;
                }

                if (copySecretBtn.disabled) {
                    return;
                }

                try {
                    await navigator.clipboard.writeText(
                        secret
                    );
                    /*
                    |--------------------------------------------------------------------------
                    | Show Success Message
                    |--------------------------------------------------------------------------
                    */
                    if (copyMessage) {
                        copyMessage.style.display =
                            'block';
                        setTimeout(function () {
                            copyMessage.style.display =
                                'none';
                        }, 2000);
                    }
                    /*
                    |--------------------------------------------------------------------------
                    | Change Copy Icon
                    |--------------------------------------------------------------------------
                    */
                    copySecretBtn.innerHTML =
                        '<i class="bi bi-check-lg"></i>';
                    setTimeout(function () {
                        copySecretBtn.innerHTML =
                            '<i class="bi bi-clipboard"></i>';
                    }, 2000);
                } catch (error) {
                    console.error(
                        'Copy secret error:',
                        error
                    );
                    showAlert(
                        'danger',
                        'Unable to copy the secret key.'
                    );
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | OTP Input
    |--------------------------------------------------------------------------
    |
    | Allow numbers only.
    |
    */
    if (confirmCode) {
        confirmCode.addEventListener(
            'input',
            function () {
                this.value =
                    this.value
                        .replace(/\D/g, '')
                        .slice(0, 6);

                this.classList.remove(
                    'is-invalid'
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Allow Enter Key
        |--------------------------------------------------------------------------
        */

        confirmCode.addEventListener(
            'keydown',
            function (event) {
                if (
                    event.key === 'Enter' &&
                    !confirmBtn.disabled
                ) {
                    event.preventDefault();
                    confirmBtn.click();
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Modal Hidden Event
    |--------------------------------------------------------------------------
    |
    | Reset temporary setup data after modal closes.
    |
    */

    if (setupModalElement) {
        setupModalElement.addEventListener(
            'hidden.bs.modal',
            function () {
                resetSetupModal();
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Disable Google 2FA
    |--------------------------------------------------------------------------
    */

    if (disableForm) {
        disableForm.addEventListener(
            'submit',
            async function (event) {
                event.preventDefault();
                /*
                |--------------------------------------------------------------------------
                | Password Input
                |--------------------------------------------------------------------------
                */
                const passwordInput =
                    disableForm.querySelector(
                        'input[name="password"]'
                    );
                if (!passwordInput) {
                    showAlert(
                        'danger',
                        'Password field not found.'
                    );
                    return;
                }

                const password =
                    passwordInput.value.trim();
                /*
                |--------------------------------------------------------------------------
                | Validate Password
                |--------------------------------------------------------------------------
                */
                if (!password) {
                    showAlert(
                        'danger',
                        'Please enter your password.'
                    );
                    passwordInput.focus();
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Prevent Multiple Submissions
                |--------------------------------------------------------------------------
                */
                if (
                    disableBtn &&
                    disableBtn.disabled
                ) {
                    return;
                }

                const originalText =
                    disableBtn
                        ? disableBtn.innerHTML
                        : '';

                /*
                |--------------------------------------------------------------------------
                | Disable Button During Request
                |--------------------------------------------------------------------------
                */

                if (disableBtn) {
                    disableBtn.disabled = true;
                    disableBtn.innerHTML = `
                        ${originalText}
                        <span
                            class="spinner-border spinner-border-sm me-1"
                            role="status"
                            aria-hidden="true"
                        ></span>

                    `;
                }

                try {
                    const response = await fetch(
                        route('admin.settings.twoFa.disable'),
                        {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN':
                                    csrfToken,
                                'Content-Type':
                                    'application/json',
                                'Accept':
                                    'application/json',
                            },
                            body: JSON.stringify({
                                password: password
                            }),
                        }
                    );
                    const data =
                        await parseResponse(response);

                    /*
                    |--------------------------------------------------------------------------
                    | Error
                    |--------------------------------------------------------------------------
                    |
                    | Keep 2FA enabled if password is wrong.
                    |
                    */
                    if (
                        !response.ok ||
                        !data.success
                    ) {
                        showAlert(
                            'danger',
                            data.message ||
                            'Incorrect password.'
                        );
                        passwordInput.value = '';
                        passwordInput.focus();
                        return;
                    }
                    /*
                    |--------------------------------------------------------------------------
                    | Successful Disable
                    |--------------------------------------------------------------------------
                    */
                    showAlert(
                        'success',
                        data.message ||
                        'Google 2FA disabled successfully.'
                    );
                    /*
                    |--------------------------------------------------------------------------
                    | Update Status Badge
                    |--------------------------------------------------------------------------
                    */
                    if (statusBadge) {
                        statusBadge.innerHTML = `
                            <span class="badge bg-secondary">
                                <i class="bi bi-shield-x me-1"></i>
                                Disabled
                            </span>
                        `;
                    }
                    /*
                    |--------------------------------------------------------------------------
                    | Hide Disable Block
                    |--------------------------------------------------------------------------
                    */
                    if (disableBlock) {
                        disableBlock.style.display =
                            'none';
                    }
                    /*
                    |--------------------------------------------------------------------------
                    | Show Enable Block
                    |--------------------------------------------------------------------------
                    */
                    if (enableBlock) {
                        enableBlock.style.display =
                            'block';
                    }
                    /*
                    |--------------------------------------------------------------------------
                    | Clear Password
                    |--------------------------------------------------------------------------
                    */
                    passwordInput.value = '';
                } catch (error) {
                    console.error(
                        '2FA disable error:',
                        error
                    );
                    showAlert(
                        'danger',
                        'Unable to disable Google 2FA right now. Please try again.'
                    );
                } finally {
                    /*
                    |--------------------------------------------------------------------------
                    | ALWAYS Restore Button
                    |--------------------------------------------------------------------------
                    */
                    if (disableBtn) {
                        disableBtn.disabled =
                            false;
                        disableBtn.innerHTML =
                            originalText;
                    }
                }
            }
        );
    }
});
