<?php

namespace App\Services;

use App\Models\ZoomConnection;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ZoomApiService
{
    public function authorizationUrl(string $state): string
    {
        return 'https://zoom.us/oauth/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $this->requiredConfig('client_id'),
            'redirect_uri' => $this->requiredConfig('redirect_uri'),
            'state' => $state,
        ]);
    }

    public function exchangeAuthorizationCode(string $code): array
    {
        return Http::asForm()
            ->withBasicAuth(
                $this->requiredConfig('client_id'),
                $this->requiredConfig('client_secret')
            )
            ->post('https://zoom.us/oauth/token', [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->requiredConfig('redirect_uri'),
            ])
            ->throw()
            ->json();
    }

    public function storeTokens(array $tokens): void
    {
        if (
            ! is_string($tokens['access_token'] ?? null)
            || ! is_string($tokens['refresh_token'] ?? null)
            || ! is_numeric($tokens['expires_in'] ?? null)
        ) {
            throw new RuntimeException("Zoom returned an invalid token response.");
        }

        $connection = ZoomConnection::query()->first() ?? new ZoomConnection();
        if (! $connection->exists) {
            $connection->id = 1;
        }

        $connection->access_token = $tokens['access_token'];
        $connection->refresh_token = $tokens['refresh_token'];
        $connection->expires_at = now()->addSeconds((int) $tokens['expires_in']);
        $connection->save();
    }

    public function forgetTokens(): void
    {
        ZoomConnection::query()->delete();
    }

    public function listMeetings(): array
    {
        $meetings = [];
        $pageToken = null;

        do {
            $query = [
                'type' => 'scheduled',
                'page_size' => 100,
            ];

            if ($pageToken) {
                $query['next_page_token'] = $pageToken;
            }

            $result = $this->request()
                ->get('users/me/meetings', $query)
                ->throw()
                ->json();

            if (! isset($result['meetings']) || ! is_array($result['meetings'])) {
                throw new RuntimeException("Zoom returned an invalid meetings response.");
            }

            $meetings = array_merge($meetings, $result['meetings']);
            $pageToken = $result['next_page_token'] ?? null;
        } while ($pageToken);

        return $meetings;
    }

    public function getMeeting(string $meetingId): array
    {
        return $this->request()
            ->get('meetings/'.rawurlencode($meetingId))
            ->throw()
            ->json();
    }

    public function createMeeting(array $data): array
    {
        $meeting = $this->request()
            ->post('users/me/meetings', $data)
            ->throw()
            ->json();

        if (empty($meeting['id']) || empty($meeting['join_url'])) {
            throw new RuntimeException('Zoom did not return the meeting ID and join link.');
        }

        return $meeting;
    }

    public function updateMeeting(string $meetingId, array $data): void
    {
        $this->request()
            ->put('meetings/'.rawurlencode($meetingId), $data)
            ->throw();
    }

    public function deleteMeeting(string $meetingId): void
    {
        $this->request()
            ->delete('meetings/'.rawurlencode($meetingId))
            ->throw();
    }

    private function request(): PendingRequest
    {
        $connection = ZoomConnection::query()->first();

        if (! $connection) {
            throw new RuntimeException('An administrator must connect Zoom before bookings can be made.');
        }

        if ($connection->expires_at->lte(now()->addMinute())) {
            $tokens = $this->refreshAccessToken($connection);
            $this->storeTokens($tokens);
            $connection = ZoomConnection::query()->findOrFail($connection->id);
        }

        return Http::baseUrl('https://api.zoom.us/v2/')
            ->acceptJson()
            ->withToken($connection->access_token);
    }

    private function refreshAccessToken(ZoomConnection $connection): array
    {
        return Http::asForm()
            ->withBasicAuth(
                $this->requiredConfig('client_id'),
                $this->requiredConfig('client_secret')
            )
            ->post('https://zoom.us/oauth/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $connection->refresh_token,
            ])
            ->throw()
            ->json();
    }

    private function requiredConfig(string $key): string
    {
        $value = config("services.zoom.{$key}");

        if (! is_string($value) || $value === '') {
            throw new RuntimeException("Zoom configuration is missing: {$key}.");
        }

        return $value;
    }
}
