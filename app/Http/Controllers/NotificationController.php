<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('notifications/Index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(20)->through(fn (UserNotification $notification) => [
                'id' => $notification->id,
                'title' => $notification->title,
                'body' => $notification->body,
                'read_at' => $notification->read_at?->toIso8601String(),
                'created_at' => $notification->created_at->toIso8601String(),
            ]),
        ]);
    }

    public function read(Request $request, UserNotification $userNotification): RedirectResponse
    {
        abort_unless($userNotification->user_id === $request->user()->id, 403);
        $userNotification->update(['read_at' => $userNotification->read_at ?? now()]);

        return back();
    }
}
