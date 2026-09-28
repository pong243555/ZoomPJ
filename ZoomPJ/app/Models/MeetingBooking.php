<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingBooking extends Model
{
    protected $fillable = [
        'user_id',
        'zoom_meeting_id',
        'topic',
        'agenda',
        'start_time',
        'duration',
        'join_url',
        'status',
        'cancelled_at',
        'cancelled_by_user_id',
    ];

    protected $casts = [
        'start_time' => 'immutable_datetime',
        'duration' => 'integer',
        'cancelled_at' => 'immutable_datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
