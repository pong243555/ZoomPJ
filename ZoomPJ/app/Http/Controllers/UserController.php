<?php

namespace App\Http\Controllers;

use App\Models\User; // import User model

class UserController extends Controller
{
    /*$meetings = Zoom::createMeeting([
        "agenda" => 'your agenda',
        "topic" => 'your topic',
        "type" => 2,
        "duration" => 60,
        "timezone" => '',
        "password" => 'set your password',
        "start_time" => 'set your start time',
        "template_id" => 'set your template id',
        "pre_schedule" => false,
        "schedule_for" => 'set your schedule for profile email ',
        "settings" => [
            'join_before_host' => false,
            'host_video' => false,
            'participant_video' => false,
            'mute_upon_entry' => false,
            'waiting_room' => false,
            'audio' => 'both',
            'auto_recording' => 'none',
            'approval_type' => 0,
        ],

    ]);*/
    public function index()
    {
        $users = User::where('role', 'user')->get();

        return view('link2', compact('users'));
    }
}
