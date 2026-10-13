<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->hasRole('super-admin'), 403);
        $filters = $request->validate(['event' => ['nullable', 'string', 'max:100']]);

        return Inertia::render('audit-logs/Index', [
            'activities' => ActivityLog::query()
                ->select(['id', 'actor_id', 'event', 'subject_type', 'subject_id', 'properties', 'created_at'])
                ->with('actor:id,name,employee_id')
                ->when($filters['event'] ?? null, fn ($query, string $event) => $query->where('event', $event))
                ->latest('id')
                ->cursorPaginate(30)
                ->through(fn (ActivityLog $activity) => [
                    'id' => $activity->id,
                    'event' => $activity->event,
                    'actor' => $activity->actor?->only(['name', 'employee_id']),
                    'subject' => $activity->subject_type ? class_basename($activity->subject_type).' #'.$activity->subject_id : null,
                    'created_at' => $activity->created_at->toIso8601String(),
                ]),
            'filters' => $filters,
        ]);
    }
}
