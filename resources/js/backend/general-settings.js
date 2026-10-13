document.addEventListener('DOMContentLoaded', function () {

    /* Pagination Limit & Password Reset Expiry */
    function bindAjaxSelect(selectId, fieldName) {
        const select = document.getElementById(selectId);
        if (!select) {
            return;
        }

        select.addEventListener('change', function () {
            const url = select.dataset.url;
            const value = select.value;

            select.disabled = true;

            // Show Processing popup
            showProcessingPopup('Processing', 'Please wait...');

            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ [fieldName]: value })
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function ({ ok, data }) {
                if (!ok || !data.status) {
                    throw new Error(data.message || 'Unable to update setting.');
                }

                // Close Processing popup
                closeProcessingPopup();

                toastr.success(data.message);
            })
            .catch(function (error) {
                toastr.error(error.message || 'Something went wrong.');
            })
            .finally(function () {
                select.disabled = false;
            });
        });
    }

    bindAjaxSelect('pagination-limit-select', 'pagination_limit');
    bindAjaxSelect('password-reset-expiry-select', 'password_reset_expiry');
    bindAjaxSelect('date_time_format_select', 'date_time_format');

    /* Maintenance Mode */
    const maintenanceSelect = document.getElementById('maintenance-mode-select');

    if (!maintenanceSelect) {
        return;
    }

    maintenanceSelect.addEventListener('change', function () {
        const url = maintenanceSelect.dataset.url;
        const value = maintenanceSelect.value;
        const turningOn = value === '1';

        function proceed() {
            maintenanceSelect.disabled = true;

            // Show Processing popup
            showProcessingPopup('Processing', 'Please wait...');

            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ maintenance_mode: value })
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function ({ ok, data }) {
                if (!ok || !data.status) {
                    throw new Error(data.message || 'Unable to update maintenance mode.');
                }

                // Close Processing popup
                closeProcessingPopup();

                toastr.success(data.message);

                // show bypass link
                if (turningOn && data.bypass_url) {
                    toastr.info(
                        'Bookmark this bypass link to access the site: ' + data.bypass_url,
                        'Maintenance Enabled',
                        { timeOut: 15000 } // 15 seconds
                    );
                }
            })
            .catch(function (error) {
                toastr.error(error.message || 'Something went wrong.');
                // revert dropdown on failure
                maintenanceSelect.value = turningOn ? '0' : '1';
            })
            .finally(function () {
                maintenanceSelect.disabled = false;
            });
        }

        if (turningOn) {
            Swal.fire({
                title: 'Enable Maintenance Mode?',
                text: 'The site will go offline for all visitors. You will get a bypass link to access the admin panel.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Enable',
                customClass: {
                    confirmButton: 'btn btn-warning',
                    cancelButton: 'btn btn-secondary'
                },
                /* buttonsStyling: false */
            }).then(function (result) {
                if (result.isConfirmed) {
                    proceed();
                } else {
                    maintenanceSelect.value = '0'; // revert
                }
            });
        } else {
            proceed();
        }
    });

    /* System Actions */
    document.querySelectorAll('.system-action-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            showConfirmation(button, function () {
                runSystemAction(button);
            });
        });
    });

    function runSystemAction(button) {
        button.disabled = true;
        const originalHtml = button.innerHTML;
        //button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Running...';

        let willRedirect = false;

        // Show Processing popup
        showProcessingPopup('Processing', 'Please wait...');

        fetch(button.dataset.url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            return response.json().then(function (data) {
                return { ok: response.ok, data: data };
            });
        })
        .then(function ({ ok, data }) {
            if (!ok || !data.status) {
                throw new Error(data.message || 'Action failed.');
            }

            // Close Processing popup
            closeProcessingPopup();

            toastr.success(data.message);

            if (data.redirect) {
                willRedirect = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Redirecting...';
                setTimeout(function () {
                    window.location.href = data.redirect;
                }, 2000);
            }
        })
        .catch(function (error) {
            toastr.error(error.message || 'Something went wrong.');
        })
        .finally(function () {
            if (!willRedirect) {
                button.disabled = false;
                button.innerHTML = originalHtml;
            }
        });
    }
});
