<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.sign_in') }} · {{ __('ui.app_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5" style="max-width: 480px">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">{{ __('ui.login_title') }}</h1>
            @include('partials.language-switcher')
        </div>
        <p class="text-secondary">{{ __('ui.shared_login_note') }}</p>
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('login') }}" class="card card-body gap-3">
            @csrf
            <div>
                <label for="email" class="form-label">{{ __('ui.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" required autofocus autocomplete="username">
            </div>
            <div>
                <label for="password" class="form-label">{{ __('ui.password') }}</label>
                <input id="password" name="password" type="password" class="form-control" required autocomplete="current-password">
            </div>
            <div class="form-check">
                <input id="remember" name="remember" type="checkbox" value="1" class="form-check-input">
                <label for="remember" class="form-check-label">{{ __('ui.remember_me') }}</label>
            </div>
            <button class="btn btn-primary" type="submit">{{ __('ui.sign_in') }}</button>
        </form>
        <p class="text-center mt-3">{{ __('ui.no_account') }} <a href="{{ route('register') }}">{{ __('ui.create_account') }}</a></p>
        <p class="text-center mt-3"><a href="{{ url('/') }}" class="link-secondary">{{ __('ui.back_home') }}</a></p>
    </main>
</body>
</html>
