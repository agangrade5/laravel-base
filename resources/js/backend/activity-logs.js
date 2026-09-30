document.addEventListener('DOMContentLoaded', function () {
    /*
    |--------------------------------------------------------------------------
    | Bootstrap Modal
    |--------------------------------------------------------------------------
    */
    const modalElement =
        document.getElementById('activity-log-modal');
    if (!modalElement) {
        return;
    }
    const modal =
        bootstrap.Modal.getOrCreateInstance(modalElement);

    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */

    const loader =
        document.getElementById('activity-log-loader');
    const content =
        document.getElementById('activity-log-content');
    const logName =
        document.getElementById('log-name');
    const logUser =
        document.getElementById('log-user');
    /* const logEvent =
        document.getElementById('log-event'); */
    const logDate =
        document.getElementById('log-date');
    const logDescription =
        document.getElementById('log-description');
    const logProperties =
        document.getElementById('log-properties');

    /*
    |--------------------------------------------------------------------------
    | Reset Modal
    |--------------------------------------------------------------------------
    */
    function resetModal() {
        logName.textContent = 'N/A';
        logUser.textContent = 'N/A';
        //logEvent.textContent = 'N/A';
        logDate.textContent = 'N/A';
        logDescription.textContent = 'N/A';
        logProperties.textContent = '{}';
    }
    /*
    |--------------------------------------------------------------------------
    | Show Loader
    |--------------------------------------------------------------------------
    */
    function showLoader() {
        loader.classList.remove('d-none');
        content.classList.add('d-none');
    }

    /*
    |--------------------------------------------------------------------------
    | Hide Loader
    |--------------------------------------------------------------------------
    */
    function hideLoader() {
        loader.classList.add('d-none');
        content.classList.remove('d-none');
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW ACTIVITY LOG
    |--------------------------------------------------------------------------
    */
    document
        .querySelectorAll('.view-activity-log')
        .forEach(function (button) {
            button.addEventListener('click', async function () {
                const url =
                    button.dataset.url;
                if (!url) {
                    toastr.error(
                        'Activity log URL not found.'
                    );
                    return;
                }
                resetModal();
                showLoader();
                modal.show();
                try {
                    const response =
                        await fetch(url, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With':
                                    'XMLHttpRequest'
                            }
                        });
                    const result =
                        await response.json();
                    if (!response.ok || !result.status) {
                        throw new Error(
                            result.message ||
                            'Unable to load activity log.'
                        );
                    }
                    const log =
                        result.data;
                    /*
                    |--------------------------------------------------------------------------
                    | Populate Modal
                    |--------------------------------------------------------------------------
                    */
                    logName.textContent =
                        log.log_name ?? 'N/A';
                    logUser.textContent =
                        log.causer ?? 'System';
                    /* logEvent.textContent =
                        log.event ?? 'N/A'; */
                    logDate.textContent =
                        log.created_at ?? 'N/A';
                    logDescription.textContent =
                        log.description ?? 'N/A';
                    logProperties.textContent =
                        JSON.stringify(
                            log.properties ?? {},
                            null,
                            4
                        );
                } catch (error) {
                    console.error(error);
                    modal.hide();
                    toastr.error(
                        error.message ||
                        'Unable to load activity log.'
                    );
                } finally {
                    hideLoader();
                }
            });
        });
    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */
    const searchInput = document.getElementById('activity-log-search');

    if (!searchInput) {
        return;
    }

    let searchTimer;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            const search = searchInput.value.trim();
            const url = new URL(window.location.href);
            if (search) {
                url.searchParams.set('search', search);
            } else {
                url.searchParams.delete('search');
            }
            // Search by page
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }, 1000);
    });
});
