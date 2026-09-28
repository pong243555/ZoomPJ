<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.browse_availability') }} · {{ __('ui.app_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
            <a class="navbar-brand mb-0" href="{{ url('/') }}">{{ __('ui.app_name') }}</a>
            <div class="d-flex align-items-center gap-3">
                @include('partials.language-switcher')
                @auth
                    @if (auth()->user()->role === 'user')
                        <a class="btn btn-outline-light btn-sm" href="{{ route('bookings.index') }}">{{ __('ui.my_bookings') }}</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-light btn-sm" type="submit">{{ __('ui.sign_out') }}</button>
                    </form>
                @else
                    <a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">{{ __('ui.sign_in') }}</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <header class="mb-4">
            <h1 class="h2 mb-1">{{ __('ui.browse_availability') }}</h1>
            <p class="text-secondary mb-0">{{ __('ui.availability_public_intro') }}</p>
        </header>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @if (! $isConnected)
            <div class="alert alert-warning">{{ __('ui.zoom_not_connected') }}</div>
        @else
            <section class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('availability.index') }}" class="row g-3 align-items-end">
                        <div class="col-sm-6 col-md-4">
                            <label for="date" class="form-label">{{ __('ui.choose_date') }}</label>
                            <input id="date" name="date" type="date" class="form-control" min="{{ now(config('app.timezone'))->toDateString() }}" value="{{ $date->toDateString() }}" required>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <label for="duration" class="form-label">{{ __('ui.duration') }}</label>
                            <select id="duration" name="duration" class="form-select">
                                @foreach ([30, 60, 90, 120] as $minutes)
                                    <option value="{{ $minutes }}" @selected($duration === $minutes)>{{ $minutes }} {{ __('ui.minutes') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary" type="submit">{{ __('ui.show_available_times') }}</button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0">{{ $date->locale(app()->getLocale())->translatedFormat('l, F j, Y') }}</h2>
                    <p class="small text-secondary mb-0">{{ __('ui.timezone_label', ['timezone' => config('app.timezone')]) }}</p>
                </div>
                <div class="card-body">
                    @if (count($slots))
                        <p class="text-secondary">{{ __('ui.available_times_intro', ['duration' => $duration]) }}</p>
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-2">
                            @foreach ($slots as $slot)
                                <div class="col">
                                    <form method="POST" action="{{ route('availability.continue') }}">
                                        @csrf
                                        <input type="hidden" name="start_time" value="{{ $slot->format('Y-m-d\TH:i') }}">
                                        <input type="hidden" name="duration" value="{{ $duration }}">
                                        <button class="btn btn-outline-success w-100" type="submit">{{ $slot->locale(app()->getLocale())->translatedFormat('g:i A') }}</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-light border mb-0">{{ __('ui.no_open_times') }}</div>
                    @endif
                </div>
            </section>

            <p class="small text-secondary">{{ __('ui.availability_privacy_note') }}</p>
        @endif

        <div class="d-flex flex-wrap gap-2 mt-4">
            @auth
                @if (auth()->user()->role === 'user')
                    <a class="btn btn-outline-primary" href="{{ route('bookings.index') }}">{{ __('ui.my_bookings') }}</a>
                @endif
            @else
                <span class="align-self-center text-secondary">{{ __('ui.booking_requires_account') }}</span>
                <a class="btn btn-primary" href="{{ route('register') }}">{{ __('ui.create_account') }}</a>
                <a class="btn btn-outline-primary" href="{{ route('login') }}">{{ __('ui.sign_in') }}</a>
            @endauth
        </div>
    </main>
</body>
</html>
