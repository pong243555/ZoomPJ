<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.app_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="{{ url('/') }}">{{ __('ui.app_name') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <a class="nav-link" href="{{ route('login') }}">{{ __('ui.sign_in') }}</a>
                    @auth
                        @if (Auth::user()->role === 'admin')
                            <a class="btn btn-primary btn-sm" href="{{ route('zoom.index') }}">{{ __('ui.open_dashboard') }}</a>
                        @else
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="btn btn-outline-light btn-sm" type="submit">{{ __('ui.sign_out') }}</button>
                            </form>
                        @endif
                    @endauth
                    @include('partials.language-switcher')
                </div>
            </div>
        </div>
    </nav>

    <main>
        <section class="container py-5">
            <div class="row align-items-center g-5 py-lg-5">
                <div class="col-lg-7">
                    <span class="badge text-bg-primary mb-3">{{ __('ui.app_name') }}</span>
                    <h1 class="display-4 fw-bold">{{ __('ui.home_title') }}</h1>
                    <p class="lead text-secondary mt-3">
                        {{ __('ui.home_intro') }}
                    </p>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a class="btn btn-primary btn-lg" href="{{ route('login') }}">{{ __('ui.sign_in') }}</a>
                        <a class="btn btn-outline-secondary btn-lg" href="{{ url('/link1') }}">{{ __('ui.features') }}</a>
                    </div>
                    <p class="small text-secondary mt-3 mb-0">{{ __('ui.admin_only_note') }}</p>
                </div>
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-4">{{ __('ui.manage_meeting') }}</h2>
                            <div class="d-flex gap-3 mb-3">
                                <span class="badge rounded-pill text-bg-primary align-self-start">1</span>
                                <div><strong>{{ __('ui.connect_step') }}</strong><div class="text-secondary small">{{ __('ui.connect_step_description') }}</div></div>
                            </div>
                            <div class="d-flex gap-3 mb-3">
                                <span class="badge rounded-pill text-bg-primary align-self-start">2</span>
                                <div><strong>{{ __('ui.schedule_step') }}</strong><div class="text-secondary small">{{ __('ui.schedule_step_description') }}</div></div>
                            </div>
                            <div class="d-flex gap-3">
                                <span class="badge rounded-pill text-bg-primary align-self-start">3</span>
                                <div><strong>{{ __('ui.share_step') }}</strong><div class="text-secondary small">{{ __('ui.share_step_description') }}</div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="container py-4 border-top text-secondary small">
        {{ __('ui.app_name') }} · {{ __('ui.footer_note') }}
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
