<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function toDisplayArray(): array
    {
        $actor = $this->relationLoaded('actor') ? $this->actor : null;
        $actorName = trim((string) ($actor?->name ?? $this->actor_name));
        $avatarName = $actorName !== '' ? $actorName : __('messages.notifications.system_actor');
        $actorAvatarUrl = $actor?->avatar_url
            ?? 'data:image/svg+xml;charset=UTF-8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 96 96"><rect width="96" height="96" rx="24" fill="#ec4899"/><text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#fff" font-family="Arial, sans-serif" font-size="28" font-weight="800">' . e(mb_substr($avatarName, 0, 2)) . '</text></svg>');

        return [
            'id' => $this->id,
            'message' => __($this->message_key, [
                'actor' => $this->actor_name,
                'subject' => $this->subject_name,
                'type' => __('messages.notifications.type_' . $this->subject_type),
            ]),
            'actor_name' => $avatarName,
            'actor_avatar_url' => $actorAvatarUrl,
            'type' => 'success',
            'timestamp' => optional($this->created_at)->getTimestampMs(),
        ];
    }
}
