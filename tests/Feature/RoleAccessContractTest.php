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
        'employee',
    ];

    /** @var list<string> */
    private const LEARNER_AREA_ROLE_SLUGS = [
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
            'employee' => 'learner',
        ] as $roleSlug => $mode) {
            $this->actingAs($this->userForRole($roleSlug))
                ->get(route('dashboard'))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('Dashboard')
                    ->where('mode', $mode)
                    ->where('auth.isTrainer', $roleSlug === 'trainer'));
        }
    }

    public function test_role_catalog_contains_only_the_four_supported_operational_roles(): void
    {
        $this->assertSame(
            ['employee', 'lms-admin', 'super-admin', 'trainer'],
            Role::query()->orderBy('slug')->pluck('slug')->all(),
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
            'reports' => ['reports.index', [], ['super-admin', 'lms-admin']],
            'trainer progress' => ['trainer-progress.index', [], ['super-admin', 'lms-admin', 'trainer']],
            'audit logs' => ['audit-logs.index', [], ['super-admin']],
            'my learning' => ['my-learning.index', [], self::LEARNER_AREA_ROLE_SLUGS],
            'my learning paths' => ['my-learning-paths.index', [], self::LEARNER_AREA_ROLE_SLUGS],
            'course library' => ['course-library.index', [], self::LEARNER_AREA_ROLE_SLUGS],
            'my progress' => ['my-progress.index', [], self::LEARNER_AREA_ROLE_SLUGS],
            'notifications' => ['notifications.index', [], self::ROLE_SLUGS],
            'certificates' => ['certificates.index', [], ['super-admin', 'lms-admin', 'employee']],
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

        foreach (['trainer'] as $roleSlug) {
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
