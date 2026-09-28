<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.all_bookings') }} · {{ __('ui.app_name') }}</title>
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
        @if ($zoomError)
            <div class="alert alert-danger" role="alert">
                <p class="mb-2">{{ __('ui.zoom_connection_error') }}</p>
                <details>
                    <summary>{{ __('ui.error_details') }}</summary>
                    <pre class="small text-wrap mb-0 mt-2">{{ $zoomError }}</pre>
                </details>
                <a class="btn btn-sm btn-outline-danger mt-3" href="{{ route('zoom.index') }}">{{ __('ui.manage_zoom') }}</a>
            </div>
        @endif
        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">&larr; {{ __('ui.admin_dashboard') }}</a>
                <h1 class="h2 mt-2 mb-0">{{ __('ui.all_bookings') }}</h1>
            </div>
            <a class="btn btn-primary" href="{{ route('zoom.index') }}">{{ __('ui.manage_meetings') }}</a>
        </div>

        <section class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h2 class="h5 mb-0">{{ __('ui.app_bookings') }}</h2></div>
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead><tr><th>{{ __('ui.meeting') }}</th><th>{{ __('ui.booking_for') }}</th><th>{{ __('ui.starts') }}</th><th>{{ __('ui.duration') }}</th><th>{{ __('ui.join') }}</th><th>{{ __('ui.actions') }}</th></tr></thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            <tr>
                                <td>
                                    {{ $booking->topic }}
                                    @if ($booking->status === 'cancelled')
                                        <div><span class="badge text-bg-secondary">{{ __('ui.cancelled') }}</span></div>
                                    @elseif ($booking->start_time->isPast())
                                        <div><span class="badge text-bg-light">{{ __('ui.completed') }}</span></div>
                                    @else
                                        <div><span class="badge text-bg-success">{{ __('ui.booked') }}</span></div>
                                    @endif
                                    @if ($booking->cancelled_at)
                                        <div class="small text-secondary">{{ __('ui.cancelled_at') }}: {{ $booking->cancelled_at->timezone(config('app.timezone'))->locale(app()->getLocale())->translatedFormat('M j, Y g:i A') }}</div>
                                    @endif
                                </td>
                                <td>{{ $booking->user?->name ?? __('ui.user') }}<div class="small text-secondary">{{ $booking->user?->email }}</div></td>
                                <td>{{ $booking->start_time->timezone(config('app.timezone'))->locale(app()->getLocale())->translatedFormat('M j, Y g:i A') }}</td>
                                <td>{{ $booking->duration }} {{ __('ui.minutes') }}</td>
                                <td><a href="{{ $booking->join_url }}" target="_blank" rel="noopener noreferrer">{{ __('ui.open_join_link') }}</a></td>
                                <td>
                                    @if ($booking->status === 'booked' && $booking->start_time->isFuture())
                                        <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" onsubmit="return confirm(@js(__('ui.confirm_cancel')))">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('ui.cancel_booking') }}</button>
                                        </form>
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-secondary py-4">{{ __('ui.no_bookings') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($bookings->hasPages())
                <div class="card-footer bg-white">{{ $bookings->links() }}</div>
            @endif
        </section>

        @if ($zoomConnected)
            <section class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h2 class="h5 mb-0">{{ __('ui.zoom_meetings') }}</h2></div>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead><tr><th>{{ __('ui.meeting') }}</th><th>{{ __('ui.starts') }}</th><th>{{ __('ui.duration') }}</th><th>{{ __('ui.join') }}</th><th>{{ __('ui.actions') }}</th></tr></thead>
                        <tbody>
                            @forelse ($zoomMeetings as $meeting)
                                <tr>
                                    <td>{{ $meeting['topic'] ?? __('ui.untitled_meeting') }}</td>
                                    <td>{{ \Illuminate\Support\Carbon::parse($meeting['start_time'])->timezone(config('app.timezone'))->locale(app()->getLocale())->translatedFormat('M j, Y g:i A') }}</td>
                                    <td>{{ $meeting['duration'] ?? '—' }} {{ __('ui.minutes') }}</td>
                                    <td>@if (!empty($meeting['join_url']))<a href="{{ $meeting['join_url'] }}" target="_blank" rel="noopener noreferrer">{{ __('ui.join') }}</a>@else{{ __('ui.unavailable') }}@endif</td>
                                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('zoom.meetings.edit', $meeting['id']) }}">{{ __('ui.edit') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary py-4">{{ __('ui.no_meetings') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @else
            <div class="alert alert-warning">{{ __('ui.zoom_not_connected') }}</div>
        @endif
    </main>
</body>
</html>
