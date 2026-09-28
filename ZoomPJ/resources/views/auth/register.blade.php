<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.create_account') }} · {{ __('ui.app_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5" style="max-width: 560px">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1 class="h3 mb-0">{{ __('ui.create_account') }}</h1>
            @include('partials.language-switcher')
        </div>
        <p class="text-secondary mb-4">{{ __('ui.registration_intro') }}</p>
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('register') }}" class="card card-body gap-3">
            @csrf
            <div class="row g-3">
                <div class="col-sm-6">
                    <label for="name" class="form-label">{{ __('ui.name') }}</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="form-control" maxlength="255" required autofocus autocomplete="given-name">
                </div>
                <div class="col-sm-6">
                    <label for="surname" class="form-label">{{ __('ui.surname') }}</label>
                    <input id="surname" name="surname" value="{{ old('surname') }}" class="form-control" maxlength="255" required autocomplete="family-name">
                </div>
            </div>
            <div>
                <label for="username" class="form-label">{{ __('ui.username') }}</label>
                <input id="username" name="username" value="{{ old('username') }}" class="form-control" maxlength="255" required autocomplete="username">
            </div>
            <div>
                <label for="email" class="form-label">{{ __('ui.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" maxlength="255" required autocomplete="email">
            </div>
            <div>
                <label for="password" class="form-label">{{ __('ui.password') }}</label>
                <input id="password" name="password" type="password" class="form-control" minlength="12" required autocomplete="new-password">
                <div class="form-text">{{ __('ui.password_requirement') }}</div>
            </div>
            <div>
                <label for="password_confirmation" class="form-label">{{ __('ui.confirm_password') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" minlength="12" required autocomplete="new-password">
            </div>
            <button class="btn btn-primary" type="submit">{{ __('ui.create_account') }}</button>
        </form>
        <p class="text-center mt-3">{{ __('ui.already_have_account') }} <a href="{{ route('login') }}">{{ __('ui.sign_in') }}</a></p>
        <p class="text-center"><a href="{{ url('/') }}" class="link-secondary">{{ __('ui.back_home') }}</a></p>
    </main>
</body>
</html>
