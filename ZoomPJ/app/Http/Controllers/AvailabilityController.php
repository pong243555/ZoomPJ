<?php

namespace App\Http\Controllers;

use App\Models\MeetingBooking;
use App\Models\ZoomConnection;
use App\Services\ZoomApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    public function __construct(private ZoomApiService $zoom)
    {
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'duration' => ['nullable', 'integer', Rule::in([30, 60, 90, 120])],
        ]);

        $timezone = config('app.timezone');
        $date = Carbon::parse($validated['date'] ?? now($timezone)->toDateString(), $timezone)->startOfDay();
        $duration = (int) ($validated['duration'] ?? 60);
        $isConnected = ZoomConnection::query()->exists();
        $busyPeriods = $isConnected ? $this->busyPeriodsForDate($date) : [];
        $slots = $this->availableSlots($date, $duration, $busyPeriods);

        return view('availability.index', [
            'date' => $date,
            'duration' => $duration,
            'isConnected' => $isConnected,
            'busyPeriods' => $busyPeriods,
            'slots' => $slots,
        ]);
    }

    public function continueToBooking(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'start_time' => ['required', 'date', 'after:now'],
            'duration' => ['required', 'integer', Rule::in([30, 60, 90, 120])],
        ]);

        $request->session()->put('requested_booking', [
            'start_time' => Carbon::parse($data['start_time'], config('app.timezone'))
                ->format('Y-m-d\TH:i'),
            'duration' => (int) $data['duration'],
        ]);

        if ($request->user()) {
            return redirect()->route(
                $request->user()->role === 'admin' ? 'admin.dashboard' : 'bookings.index'
            );
        }

        return redirect()->route('register');
    }

    private function busyPeriodsForDate(Carbon $date): array
    {
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $dayStart->copy()->addDay();
        $bookings = MeetingBooking::query()
            ->where('status', 'booked')
            ->where('start_time', '<', $dayEnd)
            ->whereRaw('DATE_ADD(start_time, INTERVAL duration MINUTE) > ?', [$dayStart->format('Y-m-d H:i:s')])
            ->get()
            ->keyBy('zoom_meeting_id');
        $periods = [];
        $seenIds = [];

        foreach ($this->zoom->listMeetings() as $meeting) {
            if (empty($meeting['id']) || empty($meeting['start_time']) || empty($meeting['duration'])) {
                continue;
            }

            $start = Carbon::parse($meeting['start_time'])->timezone(config('app.timezone'));
            $end = $start->copy()->addMinutes((int) $meeting['duration']);
            if ($start->gte($dayEnd) || $end->lte($dayStart)) {
                continue;
            }

            $periods[] = ['start' => $start, 'end' => $end];
            $seenIds[(string) $meeting['id']] = true;
        }

        foreach ($bookings as $booking) {
            if (isset($seenIds[(string) $booking->zoom_meeting_id])) {
                continue;
            }

            $start = $booking->start_time->timezone(config('app.timezone'));
            $periods[] = [
                'start' => $start,
                'end' => $start->copy()->addMinutes($booking->duration),
            ];
        }

        return $periods;
    }

    private function availableSlots(Carbon $date, int $duration, array $busyPeriods): array
    {
        $slots = [];
        $now = Carbon::now(config('app.timezone'))->addMinutes(5);
        $dayEnd = $date->copy()->addDay();

        for ($minutes = 0; $minutes < 24 * 60; $minutes += 30) {
            $start = $date->copy()->addMinutes($minutes);
            $end = $start->copy()->addMinutes($duration);

            if ($start->lt($now) || $end->gt($dayEnd)) {
                continue;
            }

            $isBusy = collect($busyPeriods)->contains(
                fn (array $period) => $start->lt($period['end']) && $end->gt($period['start'])
            );

            if (! $isBusy) {
                $slots[] = $start;
            }
        }

        return $slots;
    }
}
