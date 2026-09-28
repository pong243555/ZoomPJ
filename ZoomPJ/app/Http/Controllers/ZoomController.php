<?php

namespace App\Http\Controllers;

use App\Services\ZoomApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ZoomController extends Controller
{
    public function __construct(private ZoomApiService $zoom)
    {
    }

    public function index(): View
    {
        $meetings = session()->has('zoom_refresh_token')
            ? $this->zoom->listMeetings()
            : [];

        return view('zoom.index', [
            'meetings' => $meetings,
            'isConnected' => session()->has('zoom_refresh_token'),
        ]);
    }

    public function connect(): RedirectResponse
    {
        $state = bin2hex(random_bytes(32));
        session(['zoom_oauth_state' => $state]);

        return redirect()->away($this->zoom->authorizationUrl($state));
    }

    public function callback(Request $request): RedirectResponse
    {
        $expectedState = (string) session('zoom_oauth_state', '');
        $receivedState = (string) $request->query('state', '');
        session()->forget('zoom_oauth_state');

        if ($expectedState === '' || ! hash_equals($expectedState, $receivedState)) {
            abort(419, 'Zoom authorization state did not match. Please connect again.');
        }

        if ($request->filled('error')) {
            return redirect()
                ->route('zoom.index')
                ->with('error', __('ui.zoom_authorization_declined').': '.$request->query('error'));
        }

        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $this->zoom->storeTokens($this->zoom->exchangeAuthorizationCode($validated['code']));

        return redirect()->route('zoom.index')->with('status', __('ui.zoom_connected'));
    }

    public function disconnect(): RedirectResponse
    {
        $this->zoom->forgetTokens();

        return redirect()->route('zoom.index')->with('status', __('ui.zoom_disconnected'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedMeeting($request);
        $meeting = $this->zoom->createMeeting($this->zoomPayload($data));

        return redirect()
            ->route('zoom.index')
            ->with('status', __('ui.meeting_created'))
            ->with('created_join_url', $meeting['join_url'] ?? null);
    }

    public function edit(string $meetingId): View
    {
        return view('zoom.edit', [
            'meeting' => $this->zoom->getMeeting($meetingId),
        ]);
    }

    public function update(Request $request, string $meetingId): RedirectResponse
    {
        $this->zoom->updateMeeting(
            $meetingId,
            $this->zoomPayload($this->validatedMeeting($request))
        );

        return redirect()->route('zoom.index')->with('status', __('ui.meeting_updated'));
    }

    public function destroy(string $meetingId): RedirectResponse
    {
        $this->zoom->deleteMeeting($meetingId);

        return redirect()->route('zoom.index')->with('status', __('ui.meeting_cancelled'));
    }

    private function validatedMeeting(Request $request): array
    {
        return $request->validate([
            'topic' => ['required', 'string', 'max:200'],
            'agenda' => ['nullable', 'string', 'max:2000'],
            'start_time' => ['required', 'date', 'after:now'],
            'duration' => ['required', 'integer', 'min:1', 'max:480'],
        ]);
    }

    private function zoomPayload(array $data): array
    {
        return [
            'topic' => $data['topic'],
            'agenda' => $data['agenda'] ?? '',
            'type' => 2,
            'start_time' => \Illuminate\Support\Carbon::parse($data['start_time'])
                ->timezone('UTC')
                ->format('Y-m-d\TH:i:s\Z'),
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
        ];
    }
}
