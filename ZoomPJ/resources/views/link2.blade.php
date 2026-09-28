<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.user_accounts_title') }} · {{ __('ui.app_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('zoom.index') }}">{{ __('ui.app_name') }}</a>
            <div class="d-flex gap-2">
                @include('partials.language-switcher')
                <a class="btn btn-outline-light btn-sm" href="{{ route('zoom.index') }}">{{ __('ui.meetings') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-light btn-sm" type="submit">{{ __('ui.sign_out') }}</button>
                </form>
            </div>
        </div>
    </nav>
    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div><h1 class="h2 mb-1">{{ __('ui.user_accounts_title') }}</h1><p class="text-secondary mb-0">{{ __('ui.user_accounts_description') }}</p></div>
            <span class="badge text-bg-secondary">{{ $users->count() }} {{ __('ui.users') }}</span>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th scope="col">{{ __('ui.id') }}</th><th scope="col">{{ __('ui.name') }}</th><th scope="col">{{ __('ui.username') }}</th><th scope="col">{{ __('ui.email') }}</th></tr></thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->user_id }}</td>
                                <td>{{ trim($user->name.' '.$user->surname) }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->email }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-secondary py-5">{{ __('ui.no_users') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
