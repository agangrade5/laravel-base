<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <!--begin::Head-->
    <head>
        <!--begin::Head-->
        @include('layouts.errors.head-link')
        <!--end::Head-->

        <!--begin::Custom Css-->
        @stack('css')
        <!--end::Custom Css-->
    </head>
    <!--end::Head-->
    <!--begin::Body-->
    <body class="bg-body-tertiary">

        <!--begin::App Auth Main-->
        @yield('content')
        <!--end::App Auth Main-->

        <!-- begin::ziggy routes -->
        @routes(nonce: csp_nonce('script'))
        <!-- end::ziggy routes -->

        <!--begin::Script-->
        @include('layouts.auth.footer-scripts')
        <!--end::Script-->

        <!--begin:: Custom Script-->
        @stack('scripts')
        <!--end:: Custom Script-->
    </body>
    <!--end::Body-->
</html>
