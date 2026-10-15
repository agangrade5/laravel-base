document.addEventListener('DOMContentLoaded', function () {
    const table = document.querySelector('[data-bulk-table]');
    const bulkBtn = document.getElementById('bulk-delete-btn');

    console.log(table, bulkBtn);
    if (!table || !bulkBtn) {
        return;
    }

    const selectAll = table.querySelector('.bulk-select-all');
    const countEl = document.getElementById('bulk-selected-count');
    const items = () => Array.from(table.querySelectorAll('.bulk-select-item'));
    const checked = () => items().filter((item) => item.checked);

    function refresh() {
        const selected = checked().length;
        const total = items().length;

        countEl.textContent = selected;
        bulkBtn.classList.toggle('d-none', selected === 0);

        if (selectAll) {
            selectAll.checked = total > 0 && selected === total;
            selectAll.indeterminate = selected > 0 && selected < total;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            items().forEach((item) => (item.checked = selectAll.checked));
            refresh();
        });
    }

    table.addEventListener('change', function (event) {
        if (event.target.classList.contains('bulk-select-item')) {
            refresh();
        }
    });

    bulkBtn.addEventListener('click', function () {
        const ids = checked().map((item) => item.value);

        if (ids.length === 0) {
            return;
        }

        bulkBtn.dataset.confirmText =
            'Are you sure you want to delete ' + ids.length + ' selected record(s)? This cannot be undone.';

        showConfirmation(bulkBtn, function () {
            const originalHtml = bulkBtn.innerHTML;
            bulkBtn.disabled = true;
            bulkBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

            let reloading = false;

            fetch(bulkBtn.dataset.url, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function ({ ok, data }) {
                if (!ok || !data.status) {
                    throw new Error(data.message || 'Unable to delete selected records.');
                }

                toastr.success(data.message);
                reloading = true;

                // Reload page if we are on the last page and there are no more items on the page after deletion
                const url = new URL(window.location.href);
                const page = parseInt(url.searchParams.get('page') || '1', 10);

                if (ids.length === items().length && page > 1) {
                    url.searchParams.set('page', page - 1);
                }

                setTimeout(function () {
                    window.location.href = url.toString();
                }, 800);
            })
            .catch(function (error) {
                toastr.error(error.message || 'Something went wrong.');
            })
            .finally(function () {
                if (!reloading) {
                    bulkBtn.disabled = false;
                    bulkBtn.innerHTML = originalHtml;
                }
            });
        });
    });
});
