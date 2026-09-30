@php $isAdmin = auth()->user()->hasRole('admin'); @endphp
<!--begin::Footer-->
<footer class="app-footer">
    <!--begin::To the end-->
    {{-- <div class="float-end d-none d-sm-inline">
        Anything you want
    </div> --}}
    <!--end::To the end-->
    <!--begin::Copyright-->
    <strong>
        Copyright &copy; <?php echo date('Y'); ?>&nbsp;
        <a href="{{ $isAdmin ? route('admin.dashboard') : route('dashboard') }}" class="text-decoration-none">{{ config('app.name') }}</a>.
    </strong>
    All rights reserved.
    <!--end::Copyright-->
</footer>
<!--end::Footer-->
