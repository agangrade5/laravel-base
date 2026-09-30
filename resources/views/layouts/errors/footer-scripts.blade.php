<!--begin::Script-->
{!! \App\Helpers\UtilityHelper::returnScriptWithNonce("https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js") !!}
{!! \App\Helpers\UtilityHelper::returnScriptWithNonce("https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js") !!}
{!! \App\Helpers\UtilityHelper::returnScriptWithNonce("https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js") !!}

{!! \App\Helpers\UtilityHelper::returnScriptWithNonce(asset('assets/js/backend/admin.js')) !!}

<script nonce="{{ csp_nonce('script') }}">
    const SELECTOR_SIDEBAR_WRAPPER = ".sidebar-wrapper";
    const Default = {
        scrollbarTheme: "os-theme-light",
        scrollbarAutoHide: "leave",
        scrollbarClickScroll: true,
    };
    document.addEventListener("DOMContentLoaded", function () {
        const sidebarWrapper = document.querySelector(
            SELECTOR_SIDEBAR_WRAPPER,
        );

        // Disable OverlayScrollbars on mobile devices to prevent touch interference
        const isMobile = window.innerWidth <= 992;

        if (
            sidebarWrapper &&
            OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
            !isMobile
        ) {
            OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                scrollbars: {
                    theme: Default.scrollbarTheme,
                    autoHide: Default.scrollbarAutoHide,
                    clickScroll: Default.scrollbarClickScroll,
                },
            });
        }
    });
</script>
<script nonce="{{ csp_nonce('script') }}">
    (() => {
        "use strict";
        const mode = () =>
            document.documentElement.getAttribute("data-bs-theme") ===
            "dark"
                ? "dark"
                : "light";
        globalThis.Apex ||= {};
        const apex = globalThis.Apex;
        apex.theme = { mode: mode() };
        apex.chart = Object.assign(apex.chart || {}, {
            background: "transparent",
        });
        new MutationObserver(() => {
            const next = mode();
            apex.theme = { mode: next };
            const instances = apex._chartInstances || [];
            for (const { chart } of instances) {
                chart.updateOptions(
                    { theme: { mode: next } },
                    false,
                    false,
                );
            }
        }).observe(document.documentElement, {
            attributes: true,
            attributeFilter: ["data-bs-theme"],
        });
    })();
</script>
<!--end::Script-->

<script nonce="{{ csp_nonce('script') }}">
    document.getElementById('source-sans-css')?.addEventListener('load', function () {
        this.media = 'all';
    });
</script>
