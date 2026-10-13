<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\PublishAnnouncement;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeAnnouncements($request);

        return Inertia::render('admin/announcements/Index', [
            'announcements' => Announcement::query()->with('author:id,name')->latest()->paginate(10)->through(fn (Announcement $announcement) => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'body' => $announcement->body,
                'published_at' => $announcement->published_at?->toIso8601String(),
                'author' => $announcement->author?->only(['name']),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAnnouncements($request);
        $data = $request->validate(['title' => ['required', 'string', 'max:160'], 'body' => ['required', 'string', 'max:4000']]);
        $announcement = $request->user()->announcements()->create($data);
        PublishAnnouncement::dispatch($announcement->id)->afterCommit();

        return to_route('announcements.index');
    }

    private function authorizeAnnouncements(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('manage-enrollments'), 403);
    }
}
