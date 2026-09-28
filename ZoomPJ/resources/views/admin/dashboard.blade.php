<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.admin_dashboard') }} · {{ __('ui.app_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">{{ __('ui.admin_dashboard') }}</a>
            <div class="d-flex align-items-center gap-3">
                @include('partials.language-switcher')
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-light btn-sm" type="submit">{{ __('ui.sign_out') }}</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <header class="mb-4">
            <h1 class="h2">{{ __('ui.welcome_admin', ['name' => auth()->user()->name]) }}</h1>
            <p class="text-secondary">{{ __('ui.admin_dashboard_intro') }}</p>
        </header>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100"><div class="card-body">
                    <div class="text-secondary">{{ __('ui.zoom_connection') }}</div>
                    <div class="h4 mt-2 mb-3">{{ $zoomConnected ? __('ui.connected') : __('ui.not_connected') }}</div>
                    <a class="btn btn-primary" href="{{ route('zoom.index') }}">{{ $zoomConnected ? __('ui.manage_meetings') : __('ui.connect_zoom') }}</a>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100"><div class="card-body">
                    <div class="text-secondary">{{ __('ui.standard_users') }}</div>
                    <div class="display-6">{{ $userCount }}</div>
                    <a class="btn btn-outline-primary mt-2" href="{{ route('admin.users') }}">{{ __('ui.view_users') }}</a>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100"><div class="card-body">
                    <div class="text-secondary">{{ __('ui.upcoming_bookings') }}</div>
                    <div class="display-6">{{ $bookingCount }}</div>
                    <a class="btn btn-outline-primary mt-2" href="{{ route('admin.bookings') }}">{{ __('ui.view_all_bookings') }}</a>
                </div></div>
            </div>
        </div>

        @if (! $zoomConnected)
            <div class="alert alert-warning">{{ __('ui.zoom_not_connected') }}</div>
        @endif

        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary" href="{{ route('zoom.index') }}">{{ __('ui.manage_meetings') }}</a>
            <a class="btn btn-outline-primary" href="{{ route('admin.users') }}">{{ __('ui.user_accounts') }}</a>
            <a class="btn btn-outline-primary" href="{{ route('admin.bookings') }}">{{ __('ui.view_all_bookings') }}</a>
            <a class="btn btn-outline-secondary" href="{{ route('availability.index') }}">{{ __('ui.browse_availability') }}</a>
        </div>
    </main>
</body>
</html>
