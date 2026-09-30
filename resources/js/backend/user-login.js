function runWhenReady(fn) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fn);
    } else {
        fn();
    }
}

runWhenReady(function () {
    const emailRadio = document.getElementById('login-email');
    const phoneRadio = document.getElementById('login-phone');
    const emailSection = document.getElementById('email-section');
    const phoneSection = document.getElementById('phone-section');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');

    if (emailRadio && phoneRadio && emailSection && phoneSection && emailInput && phoneInput) {
        function toggleLoginType() {
            if (emailRadio.checked) {
                emailSection.style.display = 'block';
                phoneSection.style.display = 'none';
                emailInput.disabled = false;
                phoneInput.disabled = true;
            } else {
                emailSection.style.display = 'none';
                phoneSection.style.display = 'block';
                emailInput.disabled = true;
                phoneInput.disabled = false;
            }
        }

        emailRadio.addEventListener('change', toggleLoginType);
        phoneRadio.addEventListener('change', toggleLoginType);
        toggleLoginType();
    }

    // Country Dropdown handler
    function setupCountryDropdown(btnId, dropdownId, hiddenInputId, flagId, codeId, optionSelector) {
        const button = document.getElementById(btnId);
        const dropdown = document.getElementById(dropdownId);
        const hiddenInput = document.getElementById(hiddenInputId);
        const selectedFlag = document.getElementById(flagId);
        const selectedCode = document.getElementById(codeId);

        if (!button || !dropdown || !hiddenInput || !selectedFlag || !selectedCode) return;

        button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropdown.classList.toggle('d-none');
        });

        document.querySelectorAll(optionSelector).forEach(function (option) {
            option.addEventListener('click', function (e) {
                e.stopPropagation();
                const code = this.dataset.code;
                const iso = this.dataset.iso;
                const name = this.dataset.name;

                hiddenInput.value = code;
                selectedCode.textContent = code;
                selectedFlag.src = '/assets/images/flags/' + iso + '.svg';
                selectedFlag.alt = name;

                dropdown.classList.add('d-none');
            });
        });

        document.addEventListener('click', function (e) {
            if (!button.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('d-none');
            }
        });
    }

    setupCountryDropdown(
        'loginCountryDropdownBtn',
        'loginCountryDropdown',
        'country_code',
        'loginSelectedFlag',
        'loginSelectedCode',
        '#loginCountryDropdown .country-option'
    );
});
