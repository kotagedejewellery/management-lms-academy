<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CourseCategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeCategories($request);

        $categories = CourseCategory::query()
            ->withCount('courses')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (CourseCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'is_active' => $category->is_active,
                'courses_count' => $category->courses_count,
            ]);

        $record = $request->integer('edit')
            ? CourseCategory::query()->find($request->integer('edit'))
            : null;

        return Inertia::render('admin/courses/Categories', [
            'categories' => $categories,
            'record' => $record?->only(['id', 'name', 'is_active']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeCategories($request);
        CourseCategory::query()->create($this->validatedData($request));

        return to_route('course-categories.index');
    }

    public function update(Request $request, CourseCategory $courseCategory): RedirectResponse
    {
        $this->authorizeCategories($request);
        $courseCategory->update($this->validatedData($request, $courseCategory));

        return to_route('course-categories.index');
    }

    private function authorizeCategories(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('manage-enrollments'), 403);
    }

    /** @return array<string, mixed> */
    private function validatedData(Request $request, ?CourseCategory $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('course_categories', 'name')->ignore($category)],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
