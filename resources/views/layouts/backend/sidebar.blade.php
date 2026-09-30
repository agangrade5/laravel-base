<!--begin::Sidebar-->
<aside
    class="app-sidebar bg-body-secondary shadow"
    data-bs-theme="dark"
>
    <!--begin::Sidebar Brand-->
    <div class="sidebar-brand">
        <!--begin::Brand Link-->
        <a href="@role('admin'){{ route('admin.dashboard') }}@elserole('user'){{ route('dashboard') }}@endrole" class="brand-link">
            <!--begin::Brand Image-->
            <img src="{{ asset('assets/images/backend/logo/logo-48.png') }}" alt="{{ config('app.name') }}" class="brand-image opacity-75 shadow">
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-bold">
                {{ config('app.name') }}
            </span>
            <!--end::Brand Text-->
        </a>
        <!--end::Brand Link-->
    </div>
    <!--end::Sidebar Brand-->

    <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <nav class="mt-2" aria-label="Main navigation">
            <!--begin::Sidebar Menu-->
            {{-- Admin Menu --}}
            @role('admin')
            <ul
                class="nav sidebar-menu flex-column"
                data-lte-toggle="treeview"
                data-accordion="false"
                id="navigation"
            >
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-people"></i>
                        <p>Users</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-gear"></i>
                        <p>Settings</p>
                    </a>
                </li>
            </ul>
            @endrole

            {{-- User Menu --}}
            @role('user')
            <ul
                class="nav sidebar-menu flex-column"
                data-lte-toggle="treeview"
                data-accordion="false"
                id="navigation"
            >
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-gear"></i>
                        <p>Settings</p>
                    </a>
                </li>
            </ul>
            @endrole
            <!--end::Sidebar Menu-->
        </nav>
    </div>
    <!--end::Sidebar Wrapper-->
</aside>
<!--end::Sidebar-->
