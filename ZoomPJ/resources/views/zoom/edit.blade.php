<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.edit_meeting') }} · {{ __('ui.app_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light" lang="{{ app()->getLocale() }}">
    <main class="container py-5" style="max-width: 800px">
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('zoom.index') }}" class="text-decoration-none">&larr; {{ __('ui.back_to_meetings') }}</a>
            @include('partials.language-switcher')
        </div>
        <h1 class="h2 mt-3 mb-4">{{ __('ui.edit_meeting') }}</h1>
        @if ($errors->any())
            <div class="alert alert-danger">{{ __('ui.error_heading') }} {{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('zoom.meetings.update', $meeting['id']) }}" class="card card-body row g-3">
            @csrf
            @method('PUT')
            <div class="col-12">
                <label for="topic" class="form-label">{{ __('ui.meeting_title') }}</label>
                <input id="topic" name="topic" class="form-control" maxlength="200" value="{{ old('topic', $meeting['topic'] ?? '') }}" required>
            </div>
            <div class="col-md-8">
                <label for="start_time" class="form-label">{{ __('ui.start_time') }} ({{ config('app.timezone') }})</label>
                <input id="start_time" name="start_time" type="datetime-local" class="form-control" min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}" value="{{ old('start_time', \Illuminate\Support\Carbon::parse($meeting['start_time'])->timezone(config('app.timezone'))->format('Y-m-d\TH:i')) }}" required>
            </div>
            <div class="col-md-4">
                <label for="duration" class="form-label">{{ __('ui.duration') }}</label>
                <input id="duration" name="duration" type="number" class="form-control" min="1" max="480" value="{{ old('duration', $meeting['duration'] ?? 60) }}" required>
            </div>
            <div class="col-12">
                <label for="agenda" class="form-label">{{ __('ui.agenda') }}</label>
                <textarea id="agenda" name="agenda" class="form-control" rows="4" maxlength="2000">{{ old('agenda', $meeting['agenda'] ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <button class="btn btn-primary" type="submit">{{ __('ui.save_changes') }}</button>
                @if (!empty($meeting['join_url']))
                    <a class="btn btn-outline-secondary" href="{{ $meeting['join_url'] }}" target="_blank" rel="noopener noreferrer">{{ __('ui.open_join_link') }}</a>
                @endif
            </div>
        </form>
    </main>
</body>
</html>
