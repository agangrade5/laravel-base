(function () {
    class CountryCodeDropdown {
        constructor(wrapper) {
            this.wrapper = wrapper;
            this.button = wrapper.querySelector('.js-country-btn');
            this.dropdown = wrapper.querySelector('.js-country-dropdown');

            if (!this.button || !this.dropdown) return;

            this.hiddenInput = document.getElementById(this.button.dataset.hiddenInput);
            this.hiddenIsoInput = document.getElementById(this.button.dataset.hiddenIsoInput);
            this.flagImg = document.getElementById(this.button.dataset.flag);
            this.codeText = document.getElementById(this.button.dataset.codeText);

            this.searchInput = this.dropdown.querySelector('.country-search-input');
            this.optionsList = this.dropdown.querySelectorAll('.country-option');
            this.noResults = this.dropdown.querySelector('.country-no-results');

            // Avoid double-binding if init runs twice on same wrapper
            if (this.wrapper.dataset.ccdInitialized === '1') return;
            this.wrapper.dataset.ccdInitialized = '1';

            this.bindEvents();
        }

        bindEvents() {
            // Toggle open/close
            this.button.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpening = this.dropdown.classList.contains('d-none');

                // close any other open dropdowns
                document.querySelectorAll('.js-country-dropdown').forEach((dd) => {
                    if (dd !== this.dropdown) dd.classList.add('d-none');
                });

                this.dropdown.classList.toggle('d-none');

                if (isOpening) {
                    this.loadFlagImages();
                    this.resetSearch();
                    if (this.searchInput) {
                        setTimeout(() => this.searchInput.focus(), 50);
                    }
                }
            });

            // Prevent dropdown close when clicking inside search box
            if (this.searchInput) {
                this.searchInput.addEventListener('click', (e) => e.stopPropagation());
                this.searchInput.addEventListener('input', (e) => this.filter(e.target.value));
            }

            // Select an option
            this.optionsList.forEach((option) => {
                option.addEventListener('click', () => this.selectOption(option));
            });

            // Close on outside click
            document.addEventListener('click', (e) => {
                if (!this.wrapper.contains(e.target)) {
                    this.dropdown.classList.add('d-none');
                }
            });
        }

        // Only fires network requests for flag images the FIRST time the
        // dropdown is opened (data-src -> src). Before that, zero requests.
        loadFlagImages() {
            if (this.flagsLoaded) return;
            this.flagsLoaded = true;

            this.dropdown.querySelectorAll('img.country-flag-lazy[data-src]').forEach((img) => {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
            });
        }

        // Filter options
        filter(query) {
            query = query.toLowerCase().trim();
            let visibleCount = 0;

            this.optionsList.forEach((option) => {
                const name = (option.dataset.name || '').toLowerCase();
                const code = (option.dataset.code || '').toLowerCase();
                const matches = !query || name.includes(query) || code.includes(query);

                // NOTE: option has Bootstrap's `d-flex` class (display:flex !important),
                // so a plain inline style.display='none' gets overridden by it.
                // We must remove/add the `d-flex` class itself, not just set inline style.
                option.classList.toggle('d-none', !matches);
                option.classList.toggle('d-flex', matches);
                if (matches) visibleCount++;
            });

            if (this.noResults) {
                this.noResults.classList.toggle('d-none', visibleCount !== 0);
            }
        }

        resetSearch() {
            if (this.searchInput) this.searchInput.value = '';
            this.filter('');
        }

        selectOption(option) {
            const code = option.dataset.code;
            const iso = option.dataset.iso;
            const name = option.dataset.name;

            this.setValue(code, iso, name);
            this.dropdown.classList.add('d-none');
        }

        // Public method - useful when pre-filling data (e.g. Edit User modal)
        setValue(code, iso, name) {
            if (this.hiddenInput) this.hiddenInput.value = code;
            if (this.hiddenIsoInput) this.hiddenIsoInput.value = iso;
            if (this.codeText) this.codeText.textContent = code;
            if (this.flagImg) {
                this.flagImg.src = '/assets/images/flags/' + iso + '.svg';
                this.flagImg.alt = name || '';
            }
        }
    }

    // Registry so other scripts (e.g. edit-user-btn click handler) can grab an instance by wrapper id
    window.countryDropdownInstances = window.countryDropdownInstances || {};

    function initCountryDropdown(container = document) {
        container.querySelectorAll('.js-country-dropdown-wrapper').forEach((wrapper) => {
            const instance = new CountryCodeDropdown(wrapper);
            if (wrapper.id) {
                window.countryDropdownInstances[wrapper.id] = instance;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initCountryDropdown(document);
    });

    window.initCountryDropdown = initCountryDropdown;
    window.CountryCodeDropdown = CountryCodeDropdown;
})();
