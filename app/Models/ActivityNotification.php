<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityNotification extends Model
{
    protected $fillable = [
        'actor_id',
        'actor_name',
        'action',
        'subject_type',
        'subject_id',
        'subject_name',
        'message_key',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'actor_id' => 'integer',
            'subject_id' => 'integer',
            'meta' => 'array',
        ];
    }

    public function toDisplayArray(): array
    {
        return [
            'id' => $this->id,
            'message' => __($this->message_key, [
                'actor' => $this->actor_name,
                'subject' => $this->subject_name,
                'type' => __('messages.notifications.type_' . $this->subject_type),
            ]),
            'type' => 'success',
            'timestamp' => optional($this->created_at)->getTimestampMs(),
        ];
    }
}
