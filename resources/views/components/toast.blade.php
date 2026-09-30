@if (session('success'))
    <script nonce="{{ csp_nonce('script') }}">
        toastr.success(@json(session('success')));
    </script>
@endif

@if (session('error'))
    <script nonce="{{ csp_nonce('script') }}">
        toastr.error(@json(session('error')));
    </script>
@endif

@if (session('warning'))
    <script nonce="{{ csp_nonce('script') }}">
        toastr.warning(@json(session('warning')));
    </script>
@endif

@if (session('info'))
    <script nonce="{{ csp_nonce('script') }}">
        toastr.info(@json(session('info')));
    </script>
@endif
