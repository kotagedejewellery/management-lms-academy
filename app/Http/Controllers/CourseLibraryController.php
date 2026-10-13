<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseLibraryController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $search = trim((string) $request->query('search', ''));
        $assigned = Enrollment::query()
            ->selectRaw('1')
            ->whereColumn('enrollments.course_id', 'courses.id')
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'completed', 'not_passed'])
            ->limit(1);

        $courses = Course::query()
            ->select(['id', 'category_id', 'code', 'title', 'description', 'estimated_minutes', 'is_mandatory'])
            ->selectSub($assigned, 'is_assigned')
            ->where('status', 'published')
            ->when($user->division_id, fn ($query) => $query->where(fn ($query) => $query->whereNull('division_id')->orWhere('division_id', $user->division_id)), fn ($query) => $query->whereNull('division_id'))
            ->when($user->position_id, fn ($query) => $query->where(fn ($query) => $query->whereNull('position_id')->orWhere('position_id', $user->position_id)), fn ($query) => $query->whereNull('position_id'))
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->whereRaw('lower(title) like ?', ['%'.mb_strtolower($search).'%'])->orWhereRaw('lower(code) like ?', ['%'.mb_strtolower($search).'%'])))
            ->with('category:id,name')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Course $course) => [
                ...$course->only(['id', 'code', 'title', 'description', 'estimated_minutes', 'is_mandatory']),
                'category' => $course->category?->only(['name']),
                'is_assigned' => (bool) $course->is_assigned,
            ]);

        return Inertia::render('learning/Library', ['courses' => $courses, 'filters' => ['search' => $search]]);
    }
}
