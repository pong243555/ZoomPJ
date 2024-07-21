<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ZoomController extends Controller
{
    public function redirectToZoom()
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => 'fi1XpHy3SBO5_QcOyTTfgQ',
            'redirect_uri' => 'YOUR_REDIRECT_URI',
            'scope' => 'YOUR_SCOPES',
        ]);

        return redirect('https://zoom.us/oauth/authorize?' . $query);
    }

    public function handleZoomCallback(Request $request)
    {
        $code = $request->code;

        $response = Http::asForm()->post('https://zoom.us/oauth/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => 'YOUR_REDIRECT_URI',
            'client_id' => 'fi1XpHy3SBO5_QcOyTTfgQ',
            'client_secret' => '4JKYSTW6feZFvZTh0u7wqoc8hwilABLf',
        ]);

        $data = $response->json();

        // Store the access token and refresh token securely in your Laravel project
        session(['access_token' => $data['access_token']]);
        session(['refresh_token' => $data['refresh_token']]);

        return redirect('/');
    }

    public function makeApiRequest()
    {
        $access_token = session('access_token');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $access_token,
        ])->get('https://api.zoom.us/v2/users/me');

        dd($response->json());
    }
}
