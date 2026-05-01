<?php

namespace App\Services;

use App\Models\ActivityNotification;
use Illuminate\Contracts\Auth\Authenticatable;

class ActivityNotificationService
{
    public function log(?Authenticatable $actor, string $action, string $subjectType, ?int $subjectId, string $subjectName): ActivityNotification
    {
        $normalizedAction = in_array($action, ['created', 'updated', 'deleted', 'imported', 'deleted_all'], true)
            ? $action
            : 'updated';

        return ActivityNotification::create([
            'actor_id' => $actor?->getAuthIdentifier(),
            'actor_name' => trim((string) ($actor?->name ?? __('messages.notifications.system_actor'))),
            'action' => $normalizedAction,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'subject_name' => $subjectName,
            'message_key' => 'messages.notifications.activity_' . $normalizedAction,
            'meta' => [
                'subject_type' => $subjectType,
            ],
        ]);
    }
}
