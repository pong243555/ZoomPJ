<?php

namespace App\Http\Controllers;

use App\Models\MeetingBooking;
use App\Models\ZoomConnection;
use App\Services\ZoomApiService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Collection;

class BookingController extends Controller
{
    public function __construct(private ZoomApiService $zoom)
    {
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);
        $timezone = config('app.timezone');
        $requestedBooking = $request->session()->get('requested_booking', []);
        $month = isset($validated['month'])
            ? Carbon::createFromFormat('Y-m', $validated['month'], $timezone)->startOfMonth()
            : (isset($requestedBooking['start_time'])
                ? Carbon::parse($requestedBooking['start_time'], $timezone)->startOfMonth()
                : Carbon::now($timezone)->startOfMonth());
        $monthEnd = $month->copy()->addMonth();
        $currentUserId = (int) auth()->id();
        $monthlyBookings = MeetingBooking::query()
            ->with('user')
            ->where('status', 'booked')
            ->where('start_time', '>=', $month)
            ->where('start_time', '<', $monthEnd)
            ->get();
        $events = $this->calendarEvents(
            $monthlyBookings,
            ZoomConnection::query()->exists() ? $this->zoom->listMeetings() : [],
            $currentUserId,
            $month,
            $monthEnd
        );

        $calendarStart = $month->copy()->startOfWeek(Carbon::SUNDAY);
        $calendarEnd = $monthEnd->copy()->subDay()->endOfWeek(Carbon::SATURDAY);
        $calendarDays = [];

        for ($day = $calendarStart->copy(); $day->lte($calendarEnd); $day->addDay()) {
            $calendarDays[] = [
                'date' => $day->copy(),
                'is_current_month' => $day->month === $month->month,
                'events' => $events->get($day->toDateString(), []),
            ];
        }

        return view('bookings.index', [
            'bookings' => MeetingBooking::query()
                ->where('user_id', auth()->id())
                ->where('status', 'booked')
                ->where('start_time', '>=', now(config('app.timezone')))
                ->orderBy('start_time')
                ->get(),
            'bookingHistory' => MeetingBooking::query()
                ->where('user_id', auth()->id())
                ->where(function ($query) {
                    $query->where('status', 'cancelled')
                        ->orWhere('start_time', '<', now(config('app.timezone')));
                })
                ->orderByDesc('start_time')
                ->limit(50)
                ->get(),
            'calendarDays' => $calendarDays,
            'month' => $month,
            'previousMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
            'suggestedStart' => $requestedBooking['start_time'] ?? null,
            'suggestedDuration' => $requestedBooking['duration'] ?? 60,
            'isConnected' => ZoomConnection::query()->exists(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'topic' => ['required', 'string', 'max:200'],
            'agenda' => ['nullable', 'string', 'max:2000'],
            'start_time' => ['required', 'date', 'after:now'],
            'duration' => ['required', 'integer', 'min:1', 'max:480'],
        ]);

        if (! ZoomConnection::query()->exists()) {
            throw ValidationException::withMessages([
                'start_time' => __('ui.zoom_not_connected'),
            ]);
        }

        $start = Carbon::parse($data['start_time'], config('app.timezone'));
        $end = $start->copy()->addMinutes((int) $data['duration']);

        $booking = DB::transaction(function () use ($data, $start, $end) {
            DB::table('zoom_booking_locks')->where('id', 1)->lockForUpdate()->first();

            $this->assertSlotAvailable($start, $end);

            $meeting = $this->zoom->createMeeting([
                'topic' => $data['topic'],
                'agenda' => $data['agenda'] ?? '',
                'type' => 2,
                'start_time' => $start->copy()->utc()->format('Y-m-d\TH:i:s\Z'),
                'duration' => (int) $data['duration'],
                'timezone' => config('app.timezone'),
                'settings' => [
                    'join_before_host' => false,
                    'host_video' => false,
                    'participant_video' => false,
                    'mute_upon_entry' => true,
                    'waiting_room' => true,
                    'audio' => 'both',
                    'auto_recording' => 'none',
                ],
            ]);

            return MeetingBooking::query()->create([
                'user_id' => auth()->id(),
                'zoom_meeting_id' => (string) $meeting['id'],
                'topic' => $data['topic'],
                'agenda' => $data['agenda'] ?? null,
                'start_time' => $start,
                'duration' => (int) $data['duration'],
                'join_url' => $meeting['join_url'],
            ]);
        }, 3);

        $request->session()->forget('requested_booking');

        return redirect()
            ->route('bookings.index')
            ->with('status', __('ui.booking_created'))
            ->with('created_join_url', $booking->join_url);
    }

    public function destroy(MeetingBooking $booking): RedirectResponse
    {
        abort_unless((int) $booking->user_id === (int) auth()->id(), 404);

        if ($booking->start_time->isPast()) {
            throw ValidationException::withMessages([
                'booking' => __('ui.past_booking_cannot_cancel'),
            ]);
        }

        try {
            $this->zoom->deleteMeeting($booking->zoom_meeting_id);
        } catch (RequestException $exception) {
            report($exception);

            return redirect()
                ->route('bookings.index')
                ->with('error', __('ui.zoom_delete_failed'));
        }

        $booking->forceFill([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by_user_id' => auth()->id(),
        ])->save();

        return redirect()->route('bookings.index')->with('status', __('ui.booking_cancelled'));
    }

    private function assertSlotAvailable(Carbon $start, Carbon $end): void
    {
        $bookings = MeetingBooking::query()
            ->where('status', 'booked')
            ->where('start_time', '<', $end)
            ->whereRaw('DATE_ADD(start_time, INTERVAL duration MINUTE) > ?', [$start->format('Y-m-d H:i:s')])
            ->exists();

        if ($bookings) {
            throw ValidationException::withMessages([
                'start_time' => __('ui.slot_unavailable'),
            ]);
        }

        $meetings = $this->zoom->listMeetings();

        foreach ($meetings as $meeting) {
            if (empty($meeting['start_time']) || empty($meeting['duration'])) {
                continue;
            }

            $meetingStart = Carbon::parse($meeting['start_time'])
                ->utc()
                ->timezone(config('app.timezone'));

            $meetingEnd = $meetingStart->copy()->addMinutes((int) $meeting['duration']);

            if ($start->lt($meetingEnd) && $end->gt($meetingStart)) {
                throw ValidationException::withMessages([
                    'start_time' => __('ui.slot_unavailable'),
                ]);
            }
        }
    }

    private function calendarEvents(
        Collection $monthlyBookings,
        array $zoomMeetings,
        int $currentUserId,
        Carbon $monthStart,
        Carbon $monthEnd
    ): Collection {
        $bookingsByMeetingId = $monthlyBookings->keyBy('zoom_meeting_id');
        $eventsByDate = collect();
        $seenMeetingIds = [];

        foreach ($zoomMeetings as $meeting) {
            if (empty($meeting['id']) || empty($meeting['start_time'])) {
                continue;
            }

            $start = Carbon::parse($meeting['start_time'])->timezone(config('app.timezone'));
            if ($start->lt($monthStart) || $start->gte($monthEnd)) {
                continue;
            }

            $meetingId = (string) $meeting['id'];
            $booking = $bookingsByMeetingId->get($meetingId);
            $isMine = $booking && (int) $booking->user_id === $currentUserId;
            $event = [
                'id' => $meetingId,
                'topic' => $booking?->topic ?? ($meeting['topic'] ?? __('ui.untitled_meeting')),
                'start_time' => $start,
                'duration' => (int) ($booking?->duration ?? $meeting['duration'] ?? 0),
                'booker' => $booking?->user?->name ?? __('ui.zoom_managed_booking'),
                'is_mine' => $isMine,
                'join_url' => $isMine ? $booking->join_url : null,
            ];

            $eventsByDate->put(
                $start->toDateString(),
                array_merge($eventsByDate->get($start->toDateString(), []), [$event])
            );
            $seenMeetingIds[$meetingId] = true;
        }

        foreach ($monthlyBookings as $booking) {
            $meetingId = (string) $booking->zoom_meeting_id;
            if (isset($seenMeetingIds[$meetingId])) {
                continue;
            }

            $start = $booking->start_time->timezone(config('app.timezone'));
            $event = [
                'id' => $meetingId,
                'topic' => $booking->topic,
                'start_time' => $start,
                'duration' => $booking->duration,
                'booker' => $booking->user?->name ?? __('ui.user'),
                'is_mine' => (int) $booking->user_id === $currentUserId,
                'join_url' => (int) $booking->user_id === $currentUserId ? $booking->join_url : null,
            ];

            $eventsByDate->put(
                $start->toDateString(),
                array_merge($eventsByDate->get($start->toDateString(), []), [$event])
            );
        }

        return $eventsByDate->map(function (array $dayEvents) {
            usort($dayEvents, fn (array $left, array $right) => $left['start_time']->timestamp <=> $right['start_time']->timestamp);

            return $dayEvents;
        });
    }
}
