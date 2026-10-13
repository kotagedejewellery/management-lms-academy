<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Division;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeCourses($request);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
        ]);

        $courses = Course::query()
            ->select(['id', 'category_id', 'division_id', 'position_id', 'prerequisite_course_id', 'code', 'title', 'estimated_minutes', 'is_mandatory', 'requires_post_test', 'passing_grade', 'status'])
            ->with(['category:id,name', 'division:id,name', 'position:id,name', 'prerequisite:id,title'])
            ->withCount('modules')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(fn ($query) => $query->where('title', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Course $course) => $this->courseData($course));

        return Inertia::render('admin/courses/Index', ['courses' => $courses, 'filters' => $filters]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeCourses($request);

        return Inertia::render('admin/courses/Create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeCourses($request);
        $data = $this->validatedData($request);
        $this->ensureOrganizationTarget($data);
        $this->ensureDraft($data);
        $course = Course::query()->create($data);

        return to_route('courses.content', $course);
    }

    public function edit(Request $request, Course $course): Response
    {
        $this->authorizeCourses($request);

        return Inertia::render('admin/courses/Edit', [
            ...$this->formOptions($course),
            'course' => $this->courseData($course->load(['category:id,name', 'division:id,name', 'position:id,name', 'prerequisite:id,title'])),
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeCourses($request);
        $data = $this->validatedData($request, $course);
        $this->ensureOrganizationTarget($data);
        $this->ensurePrerequisiteDoesNotLoop($course, $data['prerequisite_course_id'] ?? null);

        if ($data['status'] === 'published') {
            if (! $course->lessons()->exists()) {
                throw ValidationException::withMessages(['status' => 'Tambahkan minimal satu lesson sebelum menerbitkan course.']);
            }
            if ($data['requires_post_test'] && ! $this->postTestReady($course)) {
                throw ValidationException::withMessages(['status' => 'Tambahkan Post-Test beserta soal dan jawaban benar sebelum menerbitkan course evaluatif.']);
            }
        }

        $data['published_at'] = $data['status'] === 'published' ? ($course->published_at ?? now()) : null;
        $course->update($data);

        return to_route('courses.content', $course);
    }

    private function authorizeCourses(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('manage-courses'), 403);
    }

    private function ensureDraft(array $data): void
    {
        if ($data['status'] === 'published') {
            throw ValidationException::withMessages(['status' => 'Buat course sebagai draf, lalu tambahkan lesson sebelum menerbitkannya.']);
        }
    }

    private function ensureOrganizationTarget(array $data): void
    {
        if (! ($data['position_id'] ?? null)) {
            return;
        }

        $positionMatchesDivision = ($data['division_id'] ?? null)
            && Position::query()
                ->whereKey($data['position_id'])
                ->where('division_id', $data['division_id'])
                ->exists();

        if (! $positionMatchesDivision) {
            throw ValidationException::withMessages(['position_id' => 'Jabatan target harus berada pada divisi target yang dipilih.']);
        }
    }

    private function ensurePrerequisiteDoesNotLoop(Course $course, ?int $prerequisiteCourseId): void
    {
        $seen = [];
        $currentId = $prerequisiteCourseId;

        while ($currentId) {
            if ($currentId === $course->id || isset($seen[$currentId])) {
                throw ValidationException::withMessages(['prerequisite_course_id' => 'Prasyarat course tidak boleh membentuk siklus.']);
            }

            $seen[$currentId] = true;
            $currentId = Course::query()->whereKey($currentId)->value('prerequisite_course_id');
        }
    }

    private function validatedData(Request $request, ?Course $course = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('course_categories', 'id')],
            'division_id' => ['nullable', 'integer', Rule::exists('divisions', 'id')],
            'position_id' => ['nullable', 'integer', Rule::exists('positions', 'id')],
            'prerequisite_course_id' => ['nullable', 'integer', Rule::exists('courses', 'id')],
            'code' => ['required', 'string', 'max:32', Rule::unique('courses', 'code')->ignore($course)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'is_mandatory' => ['required', 'boolean'],
            'requires_post_test' => ['nullable', 'boolean'],
            'passing_grade' => ['required', 'integer', 'min:0', 'max:100'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
        ]);

        $postTest = $course?->assessments()->where('type', 'post_test')->first();
        $data['requires_post_test'] = $postTest !== null || ($data['requires_post_test'] ?? $course?->requires_post_test ?? false);
        if ($postTest) {
            $data['passing_grade'] = $postTest->passing_grade;
        }

        return $data;
    }

    private function formOptions(?Course $course = null): array
    {
        return [
            'categories' => CourseCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'divisions' => Division::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'positions' => Position::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'prerequisites' => Course::query()->whereKeyNot($course?->id)->orderBy('title')->get(['id', 'title']),
        ];
    }

    private function courseData(Course $course): array
    {
        return [
            'id' => $course->id,
            'code' => $course->code,
            'title' => $course->title,
            'description' => $course->description,
            'estimated_minutes' => $course->estimated_minutes,
            'is_mandatory' => $course->is_mandatory,
            'requires_post_test' => $course->requires_post_test,
            'passing_grade' => $course->passing_grade,
            'status' => $course->status,
            'category' => $course->category?->only(['id', 'name']),
            'division' => $course->division?->only(['id', 'name']),
            'position' => $course->position?->only(['id', 'name']),
            'prerequisite' => $course->prerequisite?->only(['id', 'title']),
            'modules_count' => $course->modules_count ?? $course->modules()->count(),
        ];
    }

    private function postTestReady(Course $course): bool
    {
        $postTest = $course->assessments()->where('type', 'post_test')->first();

        return $postTest !== null
            && $postTest->questions()->exists()
            && ! $postTest->questions()->whereDoesntHave('answers', fn ($query) => $query->where('is_correct', true))->exists();
    }
}
