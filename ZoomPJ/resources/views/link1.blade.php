<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.features') }} · {{ __('ui.app_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="{{ url('/') }}">{{ __('ui.app_name') }}</a>
            <div class="d-flex align-items-center gap-3">
                @include('partials.language-switcher')
                <a class="btn btn-primary btn-sm" href="{{ route('login') }}">{{ __('ui.sign_in') }}</a>
            </div>
        </div>
    </nav>
    <main class="container py-5">
        <a href="{{ url('/') }}" class="text-decoration-none">&larr; {{ __('ui.home') }}</a>
        <h1 class="display-5 fw-bold mt-3">{{ __('ui.features_title') }}</h1>
        <p class="lead text-secondary mb-5">{{ __('ui.features_intro') }}</p>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <article class="card h-100 border-0 shadow-sm"><div class="card-body">
                    <h2 class="h5">{{ __('ui.schedule_feature') }}</h2><p class="text-secondary mb-0">{{ __('ui.schedule_feature_description') }}</p>
                </div></article>
            </div>
            <div class="col-md-6 col-lg-3">
                <article class="card h-100 border-0 shadow-sm"><div class="card-body">
                    <h2 class="h5">{{ __('ui.manage_feature') }}</h2><p class="text-secondary mb-0">{{ __('ui.manage_feature_description') }}</p>
                </div></article>
            </div>
            <div class="col-md-6 col-lg-3">
                <article class="card h-100 border-0 shadow-sm"><div class="card-body">
                    <h2 class="h5">{{ __('ui.share_feature') }}</h2><p class="text-secondary mb-0">{{ __('ui.share_feature_description') }}</p>
                </div></article>
            </div>
            <div class="col-md-6 col-lg-3">
                <article class="card h-100 border-0 shadow-sm"><div class="card-body">
                    <h2 class="h5">{{ __('ui.cancel_feature') }}</h2><p class="text-secondary mb-0">{{ __('ui.cancel_feature_description') }}</p>
                </div></article>
            </div>
        </div>
        <div class="alert alert-info mt-5">
            {{ __('ui.zoom_notice') }}
        </div>
        <a class="btn btn-primary" href="{{ route('login') }}">{{ __('ui.sign_in_to_continue') }}</a>
    </main>
</body>
</html>
