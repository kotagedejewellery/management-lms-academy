<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        if ($user->hasPermission('manage-enrollments')) {
            return $this->managementDashboard();
        }

        $enrollments = Enrollment::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'completed', 'not_passed'])
            ->with(['course:id,code,title'])
            ->orderByRaw('due_at is null, due_at')
            ->get();
        $continue = $enrollments->firstWhere('status', 'active');

        return Inertia::render('Dashboard', [
            'mode' => 'learner',
            'stats' => [
                ['label' => 'Sedang dipelajari', 'value' => $enrollments->where('status', 'active')->count()],
                ['label' => 'Course selesai', 'value' => $enrollments->where('status', 'completed')->count()],
            ],
            'continue_course' => $continue ? [
                'id' => $continue->course_id,
                'code' => $continue->course->code,
                'title' => $continue->course->title,
                'due_at' => $continue->due_at?->toDateString(),
            ] : null,
            'recent_enrollments' => [],
        ]);
    }

    private function managementDashboard(): Response
    {
        $dashboard = Cache::remember('dashboard:management', now()->addMinutes(5), function (): array {
            $recentEnrollments = Enrollment::query()
                ->with(['user:id,name', 'course:id,code,title'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Enrollment $enrollment) => [
                    'id' => $enrollment->id,
                    'employee' => $enrollment->user->name,
                    'course' => $enrollment->course->title,
                    'status' => $enrollment->status,
                    'due_at' => $enrollment->due_at?->toDateString(),
                ])
                ->all();

            return [
                'stats' => [
                    ['label' => 'Karyawan aktif', 'value' => User::query()->where('status', 'active')->count()],
                    ['label' => 'Course diterbitkan', 'value' => Course::query()->where('status', 'published')->count()],
                    ['label' => 'Assignment aktif', 'value' => Enrollment::query()->where('status', 'active')->count()],
                    ['label' => 'Course selesai', 'value' => Enrollment::query()->where('status', 'completed')->count()],
                ],
                'recent_enrollments' => $recentEnrollments,
            ];
        });

        return Inertia::render('Dashboard', [
            'mode' => 'management',
            'stats' => $dashboard['stats'],
            'continue_course' => null,
            'recent_enrollments' => $dashboard['recent_enrollments'],
        ]);
    }
}
