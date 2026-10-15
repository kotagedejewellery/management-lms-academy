<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EnrollmentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeEnrollments($request);

        return Inertia::render('admin/enrollments/Index', [
            'enrollments' => Enrollment::query()
                ->select(['id', 'user_id', 'course_id', 'status', 'due_at', 'started_at'])
                ->with(['user:id,employee_id,name', 'course:id,code,title'])
                ->orderByDesc('created_at')
                ->paginate(15)
                ->through(fn (Enrollment $enrollment) => [
                    'id' => $enrollment->id,
                    'status' => $enrollment->status,
                    'due_at' => $enrollment->due_at?->toDateString(),
                    'started_at' => $enrollment->started_at?->toDateTimeString(),
                    'user' => $enrollment->user?->only(['employee_id', 'name']),
                    'course' => $enrollment->course?->only(['code', 'title']),
                ]),
            'courses' => Course::query()->where('status', 'published')->orderBy('title')->get(['id', 'code', 'title']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeEnrollments($request);
        $data = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'course_id' => ['required', 'integer', Rule::exists('courses', 'id')],
            'due_at' => ['nullable', 'date'],
        ]);

        abort_unless(User::query()->whereKey($data['user_id'])->where('status', 'active')->exists(), 422);
        abort_unless(Course::query()->whereKey($data['course_id'])->where('status', 'published')->exists(), 422);

        Enrollment::query()->firstOrCreate(
            ['user_id' => $data['user_id'], 'course_id' => $data['course_id']],
            ['status' => 'active', 'due_at' => $data['due_at'] ?? null],
        );

        return to_route('enrollments.index');
    }

    private function authorizeEnrollments(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('manage-enrollments'), 403);
    }
}
