<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Division;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Services\LearningPathAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $users = User::query()
            ->select(['id', 'employee_id', 'hired_at', 'name', 'email', 'phone', 'division_id', 'position_id', 'branch_id', 'manager_id', 'status'])
            ->with([
                'roles:id,slug,name',
                'division:id,name',
                'position:id,name',
                'branch:id,name',
                'manager:id,name',
            ])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%")
                        ->orWhere('employee_id', 'ilike', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user) => $this->userData($user));

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('admin/users/Create', $this->formOptions($request->user()));
    }

    public function store(Request $request, LearningPathAssignmentService $assignments): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $this->validatedData($request);
        $role = $this->assignableRole($request->user(), $data['role_id']);
        unset($data['role_id'], $data['password_confirmation']);
        $data['email_verified_at'] = null;

        $user = DB::transaction(function () use ($data, $role, $assignments): User {
            $user = User::query()->create($data);
            $user->roles()->sync([$role->id]);
            $assignments->assignMatching($user);

            return $user;
        });

        $user->sendEmailVerificationNotification();
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Akun dibuat. Tautan verifikasi telah dikirim ke email kerja pengguna.']);

        return to_route('users.index');
    }

    public function edit(Request $request, User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('admin/users/Edit', [
            ...$this->formOptions($request->user(), $user),
            'user' => $this->userData($user->load(['roles:id,slug,name', 'division:id,name', 'position:id,name', 'branch:id,name', 'manager:id,name'])),
        ]);
    }

    public function update(Request $request, User $user, LearningPathAssignmentService $assignments): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $this->validatedData($request, $user);

        if ((int) ($data['manager_id'] ?? 0) === $user->id) {
            throw ValidationException::withMessages(['manager_id' => 'Manager tidak boleh merujuk ke akun yang sama.']);
        }

        if ($user->is($request->user()) && $data['status'] === 'inactive') {
            throw ValidationException::withMessages(['status' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        }

        $role = $this->assignableRole($request->user(), $data['role_id']);
        unset($data['role_id'], $data['password_confirmation']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        DB::transaction(function () use ($user, $data, $role, $assignments): void {
            $user->update($data);
            $user->roles()->sync([$role->id]);
            $assignments->assignMatching($user);
        });

        return to_route('users.index');
    }

    /** @return array<string, mixed> */
    private function validatedData(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'employee_id' => ['nullable', 'string', 'max:64', Rule::unique('users', 'employee_id')->ignore($user)],
            'hired_at' => ['nullable', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => [$user ? 'nullable' : 'required', 'string', Password::defaults(), 'confirmed'],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'division_id' => ['nullable', 'integer', Rule::exists('divisions', 'id')],
            'position_id' => ['nullable', 'integer', Rule::exists('positions', 'id')],
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')],
            'manager_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('status', 'active')],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        if ($data['position_id'] ?? null) {
            $positionMatchesDivision = ($data['division_id'] ?? null)
                && Position::query()
                    ->whereKey($data['position_id'])
                    ->where('division_id', $data['division_id'])
                    ->exists();

            if (! $positionMatchesDivision) {
                throw ValidationException::withMessages(['position_id' => 'Jabatan harus berada pada divisi yang dipilih.']);
            }
        }

        return $data;
    }

    private function assignableRole(User $actor, int $roleId): Role
    {
        $role = Role::query()->findOrFail($roleId);

        abort_unless($actor->hasRole('super-admin') || $role->slug !== 'super-admin', 403);

        return $role;
    }

    /** @return array<string, mixed> */
    private function formOptions(User $actor, ?User $exclude = null): array
    {
        return [
            'roles' => Role::query()
                ->when(! $actor->hasRole('super-admin'), fn ($query) => $query->where('slug', '!=', 'super-admin'))
                ->orderBy('name')
                ->get(['id', 'slug', 'name']),
            'divisions' => Division::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'positions' => Position::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'managers' => User::query()
                ->where('status', 'active')
                ->when($exclude, fn ($query) => $query->whereKeyNot($exclude->id))
                ->orderBy('name')
                ->get(['id', 'employee_id', 'name']),
        ];
    }

    /** @return array<string, mixed> */
    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'employee_id' => $user->employee_id,
            'hired_at' => $user->hired_at?->toDateString(),
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'role' => $user->roles->first()?->only(['id', 'slug', 'name']),
            'division' => $user->division?->only(['id', 'name']),
            'position' => $user->position?->only(['id', 'name']),
            'branch' => $user->branch?->only(['id', 'name']),
            'manager' => $user->manager?->only(['id', 'name']),
        ];
    }
}
