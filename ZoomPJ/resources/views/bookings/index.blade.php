<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.booking_page_title') }} · {{ __('ui.app_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container d-flex flex-wrap gap-3">
            <a class="navbar-brand mb-0" href="{{ url('/') }}">{{ __('ui.app_name') }}</a>
            <div class="d-flex align-items-center gap-3">
                @include('partials.language-switcher')
                <a class="btn btn-outline-light btn-sm" href="{{ route('availability.index') }}">{{ __('ui.browse_availability') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-light btn-sm" type="submit">{{ __('ui.sign_out') }}</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        <div class="mb-4">
            <h1 class="h2 mb-1">{{ __('ui.booking_page_title') }}</h1>
            <p class="text-secondary mb-0">{{ __('ui.booking_page_intro') }}</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif
        @if (session('created_join_url'))
            <div class="alert alert-info">
                {{ __('ui.join_link') }}:
                <a href="{{ session('created_join_url') }}" target="_blank" rel="noopener noreferrer">{{ session('created_join_url') }}</a>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <p class="mb-1">{{ __('ui.error_heading') }}</p>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="mb-5">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
                <div>
                    <h2 class="h4 mb-1">{{ __('ui.shared_calendar') }}</h2>
                    <p class="text-secondary mb-0">{{ __('ui.calendar_intro') }}</p>
                </div>
                <nav class="btn-group" aria-label="{{ __('ui.shared_calendar') }}">
                    <a class="btn btn-outline-primary" href="{{ route('bookings.index', ['month' => $previousMonth]) }}">&larr; {{ __('ui.previous_month') }}</a>
                    <span class="btn btn-light border fw-semibold">{{ $month->copy()->locale(app()->getLocale())->translatedFormat('F Y') }}</span>
                    <a class="btn btn-outline-primary" href="{{ route('bookings.index', ['month' => $nextMonth]) }}">{{ __('ui.next_month') }} &rarr;</a>
                </nav>
            </div>
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-bordered align-top mb-0 calendar-table">
                        <thead class="table-light">
                            <tr>
                                @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $weekday)
                                    <th scope="col" class="text-center small">{{ __('ui.weekday_'.$weekday) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (array_chunk($calendarDays, 7) as $week)
                                <tr>
                                    @foreach ($week as $day)
                                        <td class="calendar-day {{ $day['is_current_month'] ? '' : 'outside-month' }}">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-semibold">{{ $day['date']->day }}</span>
                                                @if ($day['is_current_month'] && $day['date']->isFuture())
                                                    <button type="button" class="btn btn-sm btn-link p-0 choose-date" data-book-date="{{ $day['date']->format('Y-m-d') }}">{{ __('ui.choose_date') }}</button>
                                                @endif
                                            </div>
                                            @forelse ($day['events'] as $event)
                                                <article class="calendar-event {{ $event['is_mine'] ? 'mine' : '' }}">
                                                    <div class="fw-semibold">{{ $event['start_time']->locale(app()->getLocale())->translatedFormat('g:i A') }} · {{ $event['topic'] }}</div>
                                                    <div>{{ __('ui.booking_for') }}: {{ $event['booker'] }}</div>
                                                    <div>{{ $event['duration'] }} {{ __('ui.minutes') }}</div>
                                                    @if ($event['is_mine'] && $event['join_url'])
                                                        <a href="{{ $event['join_url'] }}" target="_blank" rel="noopener noreferrer">{{ __('ui.join') }}</a>
                                                    @endif
                                                </article>
                                            @empty
                                                @if ($day['is_current_month'])
                                                    <span class="small text-secondary">{{ __('ui.no_events_this_day') }}</span>
                                                @endif
                                            @endforelse
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        @if ($isConnected)
            <section class="card border-0 shadow-sm mb-5" id="booking-form">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0">{{ __('ui.schedule_meeting') }}</h2>
                </div>
                <div class="card-body">
                    <p class="small text-secondary">{{ __('ui.booking_note') }}</p>
                    <form method="POST" action="{{ route('bookings.store') }}" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label for="topic" class="form-label">{{ __('ui.meeting_title') }}</label>
                            <input id="topic" name="topic" class="form-control" maxlength="200" value="{{ old('topic') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="start_time" class="form-label">{{ __('ui.start_time') }} ({{ config('app.timezone') }})</label>
                            <input id="start_time" name="start_time" type="datetime-local" class="form-control" min="{{ now(config('app.timezone'))->addMinutes(5)->format('Y-m-d\TH:i') }}" value="{{ old('start_time', $suggestedStart) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="duration" class="form-label">{{ __('ui.duration') }}</label>
                            <input id="duration" name="duration" type="number" class="form-control" min="1" max="480" value="{{ old('duration', $suggestedDuration) }}" required>
                        </div>
                        <div class="col-12">
                            <label for="agenda" class="form-label">{{ __('ui.agenda') }}</label>
                            <textarea id="agenda" name="agenda" class="form-control" rows="2" maxlength="2000">{{ old('agenda') }}</textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" type="submit">{{ __('ui.book_a_meeting') }}</button>
                        </div>
                    </form>
                </div>
            </section>
        @else
            <div class="alert alert-warning">{{ __('ui.zoom_not_connected') }}</div>
        @endif

        <section>
            <h2 class="h4 mb-3">{{ __('ui.my_bookings') }}</h2>
            @if ($bookings->isNotEmpty())
                <div class="row g-3">
                    @foreach ($bookings as $booking)
                        <div class="col-12">
                            <article class="card border-0 shadow-sm">
                                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <div>
                                        <h3 class="h5 mb-1">{{ $booking->topic }}</h3>
                                        @if ($booking->agenda)
                                            <p class="text-secondary mb-1">{{ $booking->agenda }}</p>
                                        @endif
                                        <p class="small text-secondary mb-0">
                                            {{ $booking->start_time->timezone(config('app.timezone'))->locale(app()->getLocale())->translatedFormat('M j, Y g:i A') }}
                                            · {{ $booking->duration }} {{ __('ui.minutes') }}
                                        </p>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-primary btn-sm" href="{{ $booking->join_url }}" target="_blank" rel="noopener noreferrer">{{ __('ui.join') }}</a>
                                        <form method="POST" action="{{ route('bookings.destroy', $booking) }}" onsubmit="return confirm(@js(__('ui.confirm_cancel')))">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm" type="submit">{{ __('ui.cancel_booking') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-light border">{{ __('ui.no_bookings') }}</div>
            @endif
        </section>

        @if ($bookingHistory->isNotEmpty())
            <section class="mt-5">
                <h2 class="h4 mb-3">{{ __('ui.booking_history') }}</h2>
                <div class="table-responsive">
                    <table class="table table-striped align-middle bg-white">
                        <thead>
                            <tr>
                                <th>{{ __('ui.meeting') }}</th>
                                <th>{{ __('ui.starts') }}</th>
                                <th>{{ __('ui.duration') }}</th>
                                <th>{{ __('ui.status') }}</th>
                                <th>{{ __('ui.cancelled_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookingHistory as $booking)
                                <tr>
                                    <td>{{ $booking->topic }}</td>
                                    <td>{{ $booking->start_time->timezone(config('app.timezone'))->locale(app()->getLocale())->translatedFormat('M j, Y g:i A') }}</td>
                                    <td>{{ $booking->duration }} {{ __('ui.minutes') }}</td>
                                    <td>{{ $booking->status === 'cancelled' ? __('ui.cancelled') : __('ui.completed') }}</td>
                                    <td>{{ $booking->cancelled_at ? $booking->cancelled_at->timezone(config('app.timezone'))->locale(app()->getLocale())->translatedFormat('M j, Y g:i A') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </main>
    <style>
        .calendar-table { min-width: 760px; table-layout: fixed; }
        .calendar-day { height: 145px; padding: .5rem !important; }
        .calendar-day.outside-month { background: #f5f6f8; color: #88909a; }
        .calendar-event { border-left: 3px solid #6c757d; background: #f1f3f5; border-radius: .25rem; font-size: .75rem; margin-bottom: .35rem; overflow-wrap: anywhere; padding: .35rem; }
        .calendar-event.mine { border-left-color: #0d6efd; background: #e7f1ff; }
        .calendar-event a { display: inline-block; margin-top: .2rem; }
    </style>
    <script>
        document.querySelectorAll('.choose-date').forEach((button) => {
            button.addEventListener('click', () => {
                const startTime = document.querySelector('#start_time');
                if (!startTime) return;
                startTime.value = `${button.dataset.bookDate}T09:00`;
                document.querySelector('#booking-form').scrollIntoView({ behavior: 'smooth' });
                startTime.focus();
            });
        });
    </script>
</body>
</html>
