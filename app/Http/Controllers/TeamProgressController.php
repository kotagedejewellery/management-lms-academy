<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Support\ReportEnrollmentQuery;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TeamProgressController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->hasPermission('view-team'), 403);
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', Rule::in(['active', 'pending_review', 'completed', 'not_passed'])]]);
        $filters = ['search' => trim($filters['search'] ?? ''), 'status' => $filters['status'] ?? '', 'course_id' => null];
        $enrollments = ReportEnrollmentQuery::make($filters)
            ->whereHas('user', fn ($query) => $query->where('manager_id', $request->user()->id))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Enrollment $enrollment) => [
                'id' => $enrollment->id,
                'progress_state' => match ($enrollment->status) {
                    'completed' => 'completed',
                    'not_passed' => 'needs_follow_up',
                    'pending_review' => 'pending_review',
                    default => 'in_progress',
                },
                'due_at' => $enrollment->due_at?->toDateString(),
                'completed_at' => $enrollment->completed_at?->toDateString(),
                'user' => $enrollment->user?->only(['employee_id', 'name']),
                'course' => $enrollment->course?->only(['code', 'title']),
            ]);

        return Inertia::render('team/Index', ['enrollments' => $enrollments, 'filters' => $filters]);
    }
}
