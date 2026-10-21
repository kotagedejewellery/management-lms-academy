<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearningProgressController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;
        $enrollments = Enrollment::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'completed', 'not_passed'])
            ->with('course:id,code,title')
            ->withCount([
                'lessonProgress as completed_lessons_count' => fn ($query) => $query->where('status', 'completed'),
                'course as lessons_count' => fn ($query) => $query->withCount('lessons'),
            ])
            ->orderByRaw('completed_at is null desc, completed_at desc')
            ->paginate(12)
            ->through(function (Enrollment $enrollment): array {
                $progress = $enrollment->lessons_count > 0 ? (int) round(($enrollment->completed_lessons_count / $enrollment->lessons_count) * 100) : 0;

                return [
                    'id' => $enrollment->id,
                    'status' => $enrollment->status === 'not_passed' ? 'needs_guidance' : $enrollment->status,
                    'completed_at' => $enrollment->completed_at?->toDateString(),
                    'completed_lessons_count' => $enrollment->completed_lessons_count,
                    'lessons_count' => $enrollment->lessons_count,
                    'progress' => $progress,
                    'course' => $enrollment->course?->only(['id', 'code', 'title']),
                ];
            });

        return Inertia::render('learning/Progress', [
            'stats' => [
                'active' => Enrollment::query()->where('user_id', $userId)->where('status', 'active')->count(),
                'completed' => Enrollment::query()->where('user_id', $userId)->where('status', 'completed')->count(),
            ],
            'enrollments' => $enrollments,
        ]);
    }
}
