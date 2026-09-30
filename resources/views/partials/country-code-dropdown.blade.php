@php
    $countries = config('countries.countries');
    $selectedCountry = collect($countries)->firstWhere('code', $selectedCode ?? '+91') ?? $countries[0];

    $wrapperId  = $idPrefix . 'countryDropdownWrapper';
    $btnId      = $idPrefix . 'countryDropdownBtn';
    $dropdownId = $idPrefix . 'countryDropdown';
    $hiddenIsoId = $idPrefix . 'country_iso';
    $hiddenId   = $idPrefix . 'country_code';
    $flagId     = $idPrefix . 'selectedFlag';
    $codeId     = $idPrefix . 'selectedCode';

    // ---- Theming (all optional, sensible light-modal defaults) ----
    $buttonClass   = $buttonClass   ?? 'form-select d-flex align-items-center justify-content-between rounded-end-0 h-100 px-2.5';
    $buttonStyle   = $buttonStyle   ?? null;
    $codeTextClass = $codeTextClass ?? 'fw-semibold text-dark small';
    $flagClass     = $flagClass     ?? 'rounded-1 border shadow-xs flex-shrink-0';
    $showChevron   = $showChevron   ?? false;
    $wrapperWidth  = $wrapperWidth  ?? '110px';
    $dropdownWidth = $dropdownWidth ?? '260px';
@endphp

<div
    class="position-relative js-country-dropdown-wrapper"
    id="{{ $wrapperId }}"
    style="width: {{ $wrapperWidth }};"
>
    {{-- Actual country iso --}}
    <input
        type="hidden"
        name="country_iso"
        id="{{ $hiddenIsoId }}"
        value="{{ $selectedCountry['iso'] }}"
    >

    {{-- Actual form value --}}
    <input
        type="hidden"
        name="country_code"
        id="{{ $hiddenId }}"
        value="{{ $selectedCountry['code'] }}"
    >

    {{-- Select box button --}}
    <button
        type="button"
        id="{{ $btnId }}"
        class="{{ $buttonClass }} js-country-btn"
        @if($buttonStyle) style="{{ $buttonStyle }}" @endif
        data-hidden-input="{{ $hiddenId }}"
        data-hidden-iso-input="{{ $hiddenIsoId }}"
        data-flag="{{ $flagId }}"
        data-code-text="{{ $codeId }}"
    >
        <div class="d-flex align-items-center gap-1.5 overflow-hidden">
            <img
                id="{{ $flagId }}"
                src="{{ asset('assets/images/flags/' . $selectedCountry['iso'] . '.svg') }}"
                width="20"
                height="15"
                alt="{{ $selectedCountry['name'] }}"
                class="{{ $flagClass }}"
            >
            <span id="{{ $codeId }}" class="{{ $codeTextClass }}">
                {{ $selectedCountry['code'] }}
            </span>
        </div>
        @if($showChevron)
            <i class="bi bi-chevron-down ms-1 text-secondary small"></i>
        @endif
    </button>

    {{-- Dropdown panel (always light/white - it's a popover, not part of the dark bar) --}}
    <div
        id="{{ $dropdownId }}"
        class="country-dropdown-menu position-absolute bg-white rounded-3 d-none mt-1 start-0 js-country-dropdown"
        style="top: 100%; width: {{ $dropdownWidth }}; max-height: 320px; overflow-y: auto; z-index: 1060; box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.15);"
    >
        {{-- Search box --}}
        <div class="p-2 border-bottom bg-white" style="position: sticky; top: 0;">
            <input
                type="text"
                class="form-control form-control-sm country-search-input"
                placeholder="Search country or code..."
                autocomplete="off"
            >
        </div>

        <div class="py-1 country-options-list">
            @foreach ($countries as $country)
                <div
                    class="country-option d-flex align-items-center justify-content-between px-3 py-2"
                    data-code="{{ $country['code'] }}"
                    data-name="{{ $country['name'] }}"
                    data-iso="{{ $country['iso'] }}"
                    style="cursor: pointer;"
                >
                    <div class="d-flex align-items-center gap-2 overflow-hidden me-3">
                        <img
                            data-src="{{ asset('assets/images/flags/' . $country['iso'] . '.svg') }}"
                            width="18"
                            height="14"
                            alt="{{ $country['name'] }}"
                            decoding="async"
                            class="rounded-1 border shadow-xs flex-shrink-0 country-flag-lazy"
                        >
                        <span class="small fw-medium text-dark text-truncate">
                            {{ $country['name'] }}
                        </span>
                    </div>
                    <span class="small text-secondary font-mono fw-semibold flex-shrink-0">
                        {{ $country['code'] }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="country-no-results text-center text-secondary small py-3 d-none">
            No country found
        </div>
    </div>
</div>
