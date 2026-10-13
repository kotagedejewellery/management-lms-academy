<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncLearningPathRule;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Division;
use App\Models\LearningPath;
use App\Models\LearningPathAssignment;
use App\Models\LearningPathRule;
use App\Models\Position;
use App\Models\User;
use App\Services\LearningPathAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LearningPathAssignmentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeAssignments($request);

        return Inertia::render('admin/learning-path-assignments/Index', [
            'assignments' => LearningPathAssignment::query()
                ->with(['user:id,employee_id,name', 'learningPath:id,name'])
                ->latest()
                ->paginate(15)
                ->through(fn (LearningPathAssignment $assignment) => [
                    'id' => $assignment->id,
                    'user' => $assignment->user?->only(['employee_id', 'name']),
                    'path' => $assignment->learningPath?->only(['name']),
                ]),
            'rules' => LearningPathRule::query()
                ->with(['learningPath:id,name', 'division:id,name', 'position:id,name', 'branch:id,name'])
                ->latest()
                ->limit(30)
                ->get()
                ->map(fn (LearningPathRule $rule) => [
                    'id' => $rule->id,
                    'path' => $rule->learningPath?->only(['name']),
                    'division' => $rule->division?->only(['name']),
                    'position' => $rule->position?->only(['name']),
                    'branch' => $rule->branch?->only(['name']),
                ]),
            'paths' => LearningPath::query()->where('status', 'published')->orderBy('name')->get(['id', 'name']),
            'divisions' => Division::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'positions' => Position::query()->where('is_active', true)->orderBy('name')->get(['id', 'division_id', 'name']),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, LearningPathAssignmentService $assignments): RedirectResponse
    {
        $this->authorizeAssignments($request);
        $data = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'learning_path_id' => ['required', 'integer', Rule::exists('learning_paths', 'id')],
        ]);

        $user = User::query()->whereKey($data['user_id'])->where('status', 'active')->firstOrFail();
        $path = LearningPath::query()->whereKey($data['learning_path_id'])->where('status', 'published')->firstOrFail();
        $assignments->assign($user, $path);

        return to_route('learning-path-assignments.index');
    }

    public function storeRule(Request $request): RedirectResponse
    {
        $this->authorizeAssignments($request);
        $data = $request->validate([
            'learning_path_id' => ['required', 'integer', Rule::exists('learning_paths', 'id')],
            'division_id' => ['required', 'integer', Rule::exists('divisions', 'id')],
            'position_id' => ['required', 'integer', Rule::exists('positions', 'id')],
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')],
        ]);

        LearningPath::query()->whereKey($data['learning_path_id'])->where('status', 'published')->firstOrFail();
        Division::query()->whereKey($data['division_id'])->where('is_active', true)->firstOrFail();
        Position::query()->whereKey($data['position_id'])->where('division_id', $data['division_id'])->where('is_active', true)->firstOrFail();
        Branch::query()->whereKey($data['branch_id'])->where('is_active', true)->firstOrFail();
        $rule = LearningPathRule::query()->firstOrCreate($data);
        $matchingEmployees = User::query()
            ->where('status', 'active')
            ->where('division_id', $rule->division_id)
            ->where('position_id', $rule->position_id)
            ->where('branch_id', $rule->branch_id)
            ->count();
        SyncLearningPathRule::dispatch($rule->id);
        ActivityLog::record($request->user(), 'learning_path_rule.saved', $rule, ['matching_employees' => $matchingEmployees]);

        return to_route('learning-path-assignments.index')->with('success', "Aturan disimpan. Sinkronisasi untuk {$matchingEmployees} karyawan yang cocok sedang diproses.");
    }

    private function authorizeAssignments(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('manage-enrollments'), 403);
    }
}
