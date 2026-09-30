@extends('layouts.errors.app')
@section('title', 'Page Not Found')
@section('content')
<!--begin:: Main Content -->
<main class="d-flex align-items-center min-vh-100 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 text-center">
                <div class="display-1 fw-bold text-primary lh-1 mb-3">
                    503
                </div>
                <h1 class="h3 mb-3">
                    Oops! Service Unavailable.
                </h1>
                <p class="text-secondary mb-4">
                    The server is currently unavailable. Please try again later.
                </p>
                @include('components.errors.navigation')
            </div>
        </div>
    </div>
</main>
<!--end:: Main Content-->
@endsection
