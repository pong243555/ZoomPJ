<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.meetings') }} · {{ __('ui.app_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light" lang="{{ app()->getLocale() }}">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('zoom.index') }}">{{ __('ui.app_name') }}</a>
            <div class="d-flex align-items-center gap-3">
                <a class="btn btn-outline-light btn-sm" href="{{ route('admin.dashboard') }}">{{ __('ui.admin_dashboard') }}</a>
                <a class="btn btn-outline-light btn-sm" href="{{ route('admin.bookings') }}">{{ __('ui.view_all_bookings') }}</a>
                @include('partials.language-switcher')
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-light btn-sm" type="submit">{{ __('ui.sign_out') }}</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h2 mb-1">{{ __('ui.meetings') }}</h1>
                <p class="text-secondary mb-0">{{ __('ui.meetings_description') }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-light btn-sm text-dark border" href="{{ route('admin.users') }}">{{ __('ui.user_accounts') }}</a>
                @if ($isConnected)
                    <form method="POST" action="{{ route('zoom.disconnect') }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-secondary" type="submit">{{ __('ui.disconnect_zoom') }}</button>
                    </form>
                @else
                    <a class="btn btn-primary" href="{{ route('zoom.connect') }}">{{ __('ui.connect_zoom') }}</a>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif
        @if ($zoomError)
            <div class="alert alert-danger" role="alert">
                <p class="mb-2">{{ __('ui.zoom_connection_error') }}</p>
                <details>
                    <summary>{{ __('ui.error_details') }}</summary>
                    <pre class="small text-wrap mb-0 mt-2">{{ $zoomError }}</pre>
                </details>
            </div>
        @endif
        @if (session('created_join_url'))
            <div class="alert alert-info">
                {{ __('ui.join_link') }}:
                <a href="{{ session('created_join_url') }}" target="_blank" rel="noopener noreferrer">
                    {{ session('created_join_url') }}
                </a>
            </div>
        @endif

        @if ($isConnected)
            <div class="card mb-4">
                <div class="card-header bg-white"><h2 class="h5 mb-0">{{ __('ui.schedule_meeting') }}</h2></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('zoom.meetings.store') }}" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label for="topic" class="form-label">{{ __('ui.meeting_title') }}</label>
                            <input id="topic" name="topic" class="form-control" maxlength="200" value="{{ old('topic') }}" required>
                            @error('topic')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label for="start_time" class="form-label">{{ __('ui.start_time') }} ({{ config('app.timezone') }})</label>
                            <input id="start_time" name="start_time" type="datetime-local" class="form-control" min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}" value="{{ old('start_time') }}" required>
                            @error('start_time')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label for="duration" class="form-label">{{ __('ui.duration') }}</label>
                            <input id="duration" name="duration" type="number" class="form-control" min="1" max="480" value="{{ old('duration', 60) }}" required>
                            @error('duration')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="agenda" class="form-label">{{ __('ui.agenda') }}</label>
                            <textarea id="agenda" name="agenda" class="form-control" rows="2" maxlength="2000">{{ old('agenda') }}</textarea>
                            @error('agenda')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" type="submit">{{ __('ui.create_meeting') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <h2 class="h4 mb-3">{{ __('ui.scheduled_meetings') }}</h2>
            @if (count($meetings))
                <div class="table-responsive">
                    <table class="table table-striped align-middle bg-white">
                        <thead>
                            <tr><th>{{ __('ui.meeting') }}</th><th>{{ __('ui.starts') }}</th><th>{{ __('ui.duration') }}</th><th>{{ __('ui.join') }}</th><th>{{ __('ui.actions') }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($meetings as $meeting)
                                <tr>
                                    <td>
                                        <strong>{{ $meeting['topic'] ?? __('ui.untitled_meeting') }}</strong>
                                        @if (!empty($meeting['agenda']))<div class="small text-secondary">{{ $meeting['agenda'] }}</div>@endif
                                    </td>
                                    <td>{{ \Illuminate\Support\Carbon::parse($meeting['start_time'])->locale(app()->getLocale())->timezone(config('app.timezone'))->translatedFormat('M j, Y g:i A') }}
                                        @php($booking = $bookingsByMeetingId->get((string) $meeting['id']))
                                        @if ($booking)
                                            <div class="small text-secondary">{{ __('ui.booking_for') }}: {{ $booking->user?->name ?? __('ui.user') }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $meeting['duration'] ?? '—' }} {{ __('ui.minutes') }}</td>
                                    <td>
                                        @if (!empty($meeting['join_url']))
                                            <a href="{{ $meeting['join_url'] }}" target="_blank" rel="noopener noreferrer">{{ __('ui.join') }}</a>
                                        @else
                                            <span class="text-secondary">{{ __('ui.unavailable') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('zoom.meetings.edit', $meeting['id']) }}">{{ __('ui.edit') }}</a>
                                        <form method="POST" action="{{ route('zoom.meetings.destroy', $meeting['id']) }}" class="d-inline" onsubmit="return confirm(@js(__('ui.confirm_cancel')))">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('ui.cancel') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-light border">{{ __('ui.no_meetings') }}</div>
            @endif
        @else
            <div class="alert alert-warning">
                {{ __('ui.connect_prompt') }}
            </div>
        @endif
    </main>
</body>
</html>