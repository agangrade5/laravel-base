import $ from 'jquery';
import 'admin-lte';
import toastr from 'toastr';
import Swal from 'sweetalert2';
import Cropper from 'cropperjs';

window.$ = $;
window.jQuery = $;

window.toastr = toastr;
window.Swal = Swal;

window.Cropper = Cropper;


// ========================================
// CSRF Token
// ========================================

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// ========================================
// Toastr Config
// ========================================

toastr.options = {
    closeButton: true,
    debug: false,
    newestOnTop: false,
    progressBar: false,
    positionClass: 'toast-top-right',
    closeHtml: '<button type="button" class="toast-close-button" aria-label="Close">&times;</button>',
    preventDuplicates: true,
    showDuration: 1500,
    hideDuration: 1500,
    timeOut: 3000,
    extendedTimeOut: 5000
};

// ========================================
// SweetAlert Confirmation
// ========================================

function showConfirmation(button, callback) {

    Swal.fire({
        title: button.dataset.confirmTitle || 'Are you sure?',
        text: button.dataset.confirmText || "You won't be able to revert this!",
        icon: button.dataset.confirmIcon || 'warning',
        showCancelButton: true,
        confirmButtonText: button.dataset.confirmButton || 'Yes, Continue',
        cancelButtonText: button.dataset.cancelButton || 'Cancel',
        customClass: {
            confirmButton: button.dataset.confirmButtonClass || 'btn btn-danger',
            cancelButton: button.dataset.confirmCancelButtonClass || 'btn btn-secondary'
        },
        //buttonsStyling: false,
        reverseButtons: true
    }).then((result) => {

        if (result.isConfirmed && callback) {
            callback();
        }

    });
}
// Add to window
window.showConfirmation = showConfirmation;

// ========================================
// Logout Confirmation
// ========================================

document.addEventListener('click', function (event) {

    const logoutButton = event.target.closest('#logout-button');

    if (!logoutButton) {
        return;
    }

    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();

    const logoutForm = document.getElementById('logout-form');

    if (!logoutForm) {
        console.error('Logout form not found.');
        return;
    }

    showConfirmation(logoutButton, () => {
        logoutForm.requestSubmit();
    });

}, true);


// ========================================
// Generic Confirmation
// ========================================

document.addEventListener('click', function (event) {

    const button = event.target.closest('[data-confirm]');

    if (!button) {
        return;
    }

    // Logout is handled separately
    if (button.id === 'logout-button') {
        return;
    }

    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();

    const form = button.closest('form');

    showConfirmation(button, () => {

        // If button is inside a form
        if (form) {
            form.requestSubmit();
            return;
        }

        // If button is an anchor
        if (button.tagName === 'A' && button.href) {
            window.location.href = button.href;
        }

    });

}, true);

// ========================================
// Global Form Submit Loader
// ========================================

$(document).on('submit', 'form', function (event) {

    const $form = $(this);

    if ($form.is('[data-no-loader]')) {
        return;
    }

    // Already submitted - prevent double submit
    if ($form.data('submitted')) {
        event.preventDefault();
        return false;
    }

    $form.data('submitted', true);

    // Find submit button
    const $button = $form.find(
        'button[type="submit"]:not([disabled]), input[type="submit"]:not([disabled])'
    ).first();

    if (!$button.length) {
        return;
    }

    // Save original button text
    const buttonText = $button.html();

    // Store original text so it can be restored if required
    $button.data('original-text', buttonText);

    // If icon-only button (no text), replace icon with spinner
    if ($button.text().trim() === '') {
        $button.html(
            `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width: 0.85rem; height: 0.85rem; border-width: 0.15em;"></span><span class="visually-hidden">Loading...</span>`
        );
    } else {
        // Hide icons inside button and append spinner
        $button.find('i, svg, .icon-idle').addClass('d-none');
        $button.append(
            `<span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span><span class="visually-hidden">Loading...</span>`
        );
    }

    // Disable button
    $button.prop('disabled', true);
});

// ========================================
// Multiple js codes
// ========================================
document.addEventListener('DOMContentLoaded', () => {

    // Toggle Password
    document.addEventListener('click', (event) => {
        const btn = event.target.closest('.toggle-password');

        if (!btn) {
            return;
        }

        const targetId = btn.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = btn.querySelector('i');

        if (!input) {
            return;
        }

        const isPassword = input.type === 'password';

        input.type = isPassword ? 'text' : 'password';

        icon.classList.toggle('bi-eye-slash', !isPassword);
        icon.classList.toggle('bi-eye', isPassword);
    });

    // Set timezone
    const timezoneInput = document.getElementById('timezone');
    if (timezoneInput) {
        timezoneInput.value =
            Intl.DateTimeFormat().resolvedOptions().timeZone;
    }
});

// ========================================
// Generic Delete Record (AJAX)
// ========================================

document.addEventListener('click', function (event) {
    const button = event.target.closest('.delete-record-btn');

    if (!button) {
        return;
    }

    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();

    const url = button.dataset.url;
    const rowId = button.dataset.rowId;

    if (!url) {
        toastr.error('Delete URL not found.');
        return;
    }
    showConfirmation(button, () => {
        // Disable button
        button.disabled = true;
        // Save original button HTML
        const originalHtml = button.innerHTML;
        // Show loader
        button.innerHTML = `
            <span
                class="spinner-border spinner-border-sm"
                role="status"
                aria-hidden="true">
            </span>
        `;

        $.ajax({
            url: url,
            type: 'DELETE',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {

                if (!response.status) {
                    throw new Error(
                        response.message ||
                        'Unable to delete record.'
                    );
                }

                // Remove row
                if (rowId) {
                    const $row = $(`#${rowId}`);
                    if ($row.length) {
                        $row.fadeOut(300, function () {
                            $(this).remove();
                        });
                    }
                }
                toastr.success(
                    response.message ||
                    'Record deleted successfully.'
                );
            },
            error: function (xhr) {
                console.error(
                    'Delete Error:',
                    xhr
                );
                button.disabled = false;
                button.innerHTML = originalHtml;
                toastr.error(
                    xhr.responseJSON?.message ||
                    'Unable to delete record. Please try again.'
                );
            }
        });
    });
}, true);
