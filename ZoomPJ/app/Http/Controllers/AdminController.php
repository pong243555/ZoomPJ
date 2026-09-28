<?php

namespace App\Http\Controllers;

use App\Models\MeetingBooking;
use App\Models\User;
use App\Models\ZoomConnection;
use App\Services\ZoomApiService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(private ZoomApiService $zoom)
    {
    }

    public function index(): View
    {
        $upcomingBookings = MeetingBooking::query()
            ->where('status', 'booked')
            ->where('start_time', '>=', now(config('app.timezone')))
            ->count();

        return view('admin.dashboard', [
            'userCount' => User::query()->where('role', 'user')->count(),
            'bookingCount' => $upcomingBookings,
            'zoomConnected' => ZoomConnection::query()->exists(),
        ]);
    }

    public function bookings(): View
    {
        $bookings = MeetingBooking::query()
            ->with('user')
            ->orderByDesc('start_time')
            ->paginate(25);

        $zoomMeetings = [];
        $zoomError = null;
        $zoomConnected = ZoomConnection::query()->exists();

        if ($zoomConnected) {
            try {
                $zoomMeetings = $this->zoom->listMeetings();
            } catch (RequestException $exception) {
                $zoomError = $exception->getMessage();
            }
        }

        return view('admin.bookings', [
            'bookings' => $bookings,
            'zoomMeetings' => $zoomMeetings,
            'zoomConnected' => $zoomConnected,
            'zoomError' => $zoomError,
        ]);
    }

    public function destroyBooking(MeetingBooking $booking): RedirectResponse
    {
        try {
            $this->zoom->deleteMeeting($booking->zoom_meeting_id);
        } catch (RequestException $exception) {
            report($exception);

            return redirect()
                ->route('admin.bookings')
                ->with('error', __('ui.zoom_delete_failed'));
        }

        $booking->forceFill([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by_user_id' => auth()->id(),
        ])->save();

        return redirect()
            ->route('admin.bookings')
            ->with('status', __('ui.booking_cancelled'));
    }
}
