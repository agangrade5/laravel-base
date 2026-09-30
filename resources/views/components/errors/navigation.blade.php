<div class="d-flex justify-content-center gap-2">
    @auth
        @if (auth()->user()->hasRole('admin'))
            <a
                href="{{ route('admin.dashboard') }}"
                class="btn btn-primary"
            >
                <i class="bi bi-speedometer2 me-1"></i>
                Go to Dashboard
            </a>
        @else
            <a
                href="{{ route('dashboard') }}"
                class="btn btn-primary"
            >
                <i class="bi bi-speedometer2 me-1"></i>
                Go to Dashboard
            </a>
        @endif
    @else
        <a
            href="{{ route('login') }}"
            class="btn btn-primary"
        >
            <i class="bi bi-box-arrow-in-right me-1"></i>
            Go to Login
        </a>
    @endauth
</div>
