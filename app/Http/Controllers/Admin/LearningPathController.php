<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LearningPath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LearningPathController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizePaths($request);
        $filters = $request->validate(['status' => ['nullable', Rule::in(['draft', 'published', 'archived'])]]);

        $paths = LearningPath::query()
            ->select(['id', 'name', 'description', 'status'])
            ->withCount('pathCourses')
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/learning-paths/Index', ['paths' => $paths, 'filters' => $filters]);
    }

    public function create(Request $request): Response
    {
        $this->authorizePaths($request);

        return Inertia::render('admin/learning-paths/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePaths($request);
        $data = $this->validatedData($request);
        $this->ensureDraft($data);
        $path = LearningPath::query()->create($data);

        return to_route('learning-paths.content', $path);
    }

    public function edit(Request $request, LearningPath $learningPath): Response
    {
        $this->authorizePaths($request);

        return Inertia::render('admin/learning-paths/Edit', ['learningPath' => $learningPath->only(['id', 'name', 'description', 'status'])]);
    }

    public function update(Request $request, LearningPath $learningPath): RedirectResponse
    {
        $this->authorizePaths($request);
        $data = $this->validatedData($request, $learningPath);

        if ($data['status'] === 'published') {
            if (! $learningPath->pathCourses()->exists()) {
                throw ValidationException::withMessages(['status' => 'Tambahkan minimal satu course sebelum menerbitkan learning path.']);
            }

            if ($learningPath->pathCourses()->whereHas('course', fn ($query) => $query->where('status', '!=', 'published'))->exists()) {
                throw ValidationException::withMessages(['status' => 'Semua course dalam learning path harus sudah dipublikasikan.']);
            }
        }

        $data['published_at'] = $data['status'] === 'published' ? ($learningPath->published_at ?? now()) : null;
        $learningPath->update($data);

        return to_route('learning-paths.content', $learningPath);
    }

    private function authorizePaths(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('manage-learning-paths'), 403);
    }

    private function ensureDraft(array $data): void
    {
        if ($data['status'] === 'published') {
            throw ValidationException::withMessages(['status' => 'Buat learning path sebagai draf, lalu tambahkan course sebelum menerbitkannya.']);
        }
    }

    private function validatedData(Request $request, ?LearningPath $learningPath = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('learning_paths', 'name')->ignore($learningPath)],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
        ]);
    }
}
