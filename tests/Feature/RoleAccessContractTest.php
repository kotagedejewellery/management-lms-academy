<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RoleAccessContractTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private const ROLE_SLUGS = [
        'super-admin',
        'lms-admin',
        'trainer',
        'manager',
        'employee',
        'management',
    ];

    /** @var list<string> */
    private const LEARNER_AREA_ROLE_SLUGS = [
        'super-admin',
        'lms-admin',
        'manager',
        'employee',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_every_role_receives_its_expected_dashboard(): void
    {
        foreach ([
            'super-admin' => 'operations',
            'lms-admin' => 'operations',
            'trainer' => 'trainer',
            'manager' => 'manager',
            'employee' => 'learner',
            'management' => 'executive',
        ] as $roleSlug => $mode) {
            $this->actingAs($this->userForRole($roleSlug))
                ->get(route('dashboard'))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('Dashboard')
                    ->where('mode', $mode)
                    ->where('auth.isTrainer', $roleSlug === 'trainer')
                    ->where('auth.isManagement', $roleSlug === 'management'));
        }
    }

    public function test_manager_role_uses_the_team_manager_label(): void
    {
        $this->assertSame(
            'Manager Tim',
            Role::query()->where('slug', 'manager')->sole()->name,
        );
    }

    public function test_role_has_the_expected_access_to_each_critical_module(): void
    {
        $modules = [
            'course list' => ['courses.index', [], ['super-admin', 'lms-admin', 'trainer']],
            'course creation' => ['courses.create', [], ['super-admin', 'lms-admin']],
            'course categories' => ['course-categories.index', [], ['super-admin', 'lms-admin']],
            'learning paths' => ['learning-paths.index', [], ['super-admin', 'lms-admin']],
            'users' => ['users.index', [], ['super-admin', 'lms-admin']],
            'organization' => ['organization.index', ['type' => 'divisions'], ['super-admin', 'lms-admin']],
            'assignments' => ['enrollments.index', [], ['super-admin', 'lms-admin']],
            'announcements' => ['announcements.index', [], ['super-admin', 'lms-admin']],
            'reports' => ['reports.index', [], ['super-admin', 'lms-admin', 'management']],
            'trainer progress' => ['trainer-progress.index', [], ['super-admin', 'lms-admin', 'trainer', 'management']],
            'team progress' => ['team-progress.index', [], ['super-admin', 'manager']],
            'audit logs' => ['audit-logs.index', [], ['super-admin']],
            'my learning' => ['my-learning.index', [], self::LEARNER_AREA_ROLE_SLUGS],
            'my learning paths' => ['my-learning-paths.index', [], self::LEARNER_AREA_ROLE_SLUGS],
            'course library' => ['course-library.index', [], self::LEARNER_AREA_ROLE_SLUGS],
            'my progress' => ['my-progress.index', [], self::LEARNER_AREA_ROLE_SLUGS],
            'notifications' => ['notifications.index', [], self::ROLE_SLUGS],
            'certificates' => ['certificates.index', [], self::LEARNER_AREA_ROLE_SLUGS],
        ];

        foreach ($modules as $module => [$route, $parameters, $allowedRoles]) {
            foreach (self::ROLE_SLUGS as $roleSlug) {
                $response = $this->actingAs($this->userForRole($roleSlug))->get(route($route, $parameters));

                $this->assertSame(
                    in_array($roleSlug, $allowedRoles, true) ? 200 : 403,
                    $response->getStatusCode(),
                    "Akses {$roleSlug} ke {$module} harus tetap sesuai kontrak.",
                );
            }
        }
    }

    public function test_operational_roles_cannot_mutate_the_learner_flow(): void
    {
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        $course = Course::query()->create(['category_id' => $category->id, 'code' => 'LEARNER-001', 'title' => 'Course learner', 'status' => 'published']);
        $lesson = $course->modules()->create(['title' => 'Modul 1', 'sequence' => 1])
            ->lessons()->create(['title' => 'Materi', 'type' => 'article', 'content' => 'Isi materi', 'is_required' => true, 'sequence' => 1]);

        foreach (['trainer', 'management'] as $roleSlug) {
            $this->actingAs($this->userForRole($roleSlug))
                ->post(route('my-learning.lessons.complete', [$course, $lesson]))
                ->assertForbidden();
        }
    }

    private function userForRole(string $roleSlug): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('slug', $roleSlug)->sole());

        return $user;
    }
}
