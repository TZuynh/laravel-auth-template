<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use Illuminate\Http\JsonResponse;

class ActivityNotificationController extends Controller
{
    public function destroy(ActivityNotification $notification): JsonResponse
    {
        $notification->delete();

        return response()->json(['ok' => true]);
    }

    public function clear(): JsonResponse
    {
        ActivityNotification::query()->delete();

        return response()->json(['ok' => true]);
    }
}
