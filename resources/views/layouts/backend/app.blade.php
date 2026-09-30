<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <!--begin::Head-->
    <head>
        <!--begin::Head-->
        @include('layouts.backend.head-link')
        <!--end::Head-->

        <!--begin::Custom Css-->
        @stack('css')
        <!--end::Custom Css-->
    </head>
    <!--end::Head-->
    <!--begin::Body-->
    <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
        <!--begin::App Wrapper-->
        <div class="app-wrapper">

            <!--begin::Header-->
            @include('layouts.backend.header')
            <!--end::Header-->

            <!--begin::Sidebar-->
            @include('layouts.backend.sidebar')
            <!--end::Sidebar-->

            <!--begin::App Main-->
            <main class="app-main">
                @yield('content')
            </main>
            <!--end::App Main-->

            <!--begin::Footer-->
            @include('layouts.backend.footer')
            <!--end::Footer-->

        </div>
        <!--end::App Wrapper-->

        <!-- begin::ziggy routes -->
        @routes(nonce: csp_nonce('script'))
        <!-- end::ziggy routes -->

        <!--begin::Script-->
        @include('layouts.backend.footer-scripts')
        <!--end::Script-->

        <!--begin:: Custom Script-->
        @stack('scripts')
        <!--end:: Custom Script-->

        <!-- begin::cookie consent -->
        {{-- @include('cookie-consent::index') --}}
        <!-- end::cookie consent -->

        <!-- begin::toast message component -->
        <x-toast />
        <!-- end::toast message component -->
    </body>
    <!--end::Body-->
</html>
