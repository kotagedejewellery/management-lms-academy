<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CourseLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_only_sees_generic_or_matching_organization_courses(): void
    {
        $division = Division::query()->create(['code' => 'OPS', 'name' => 'Operasional', 'is_active' => true]);
        $otherDivision = Division::query()->create(['code' => 'HR', 'name' => 'Human Resources', 'is_active' => true]);
        $employee = User::factory()->create(['division_id' => $division->id]);
        $category = CourseCategory::query()->create(['name' => 'Onboarding', 'is_active' => true]);
        Course::query()->create(['category_id' => $category->id, 'code' => 'GEN-001', 'title' => 'Umum', 'status' => 'published']);
        Course::query()->create(['category_id' => $category->id, 'division_id' => $division->id, 'code' => 'OPS-001', 'title' => 'Operasional', 'status' => 'published']);
        Course::query()->create(['category_id' => $category->id, 'division_id' => $otherDivision->id, 'code' => 'HR-001', 'title' => 'HR Rahasia', 'status' => 'published']);

        $this->actingAs($employee)->get(route('course-library.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('learning/Library')
            ->has('courses.data', 2)
            ->where('courses.data.0.code', 'OPS-001')
            ->where('courses.data.1.code', 'GEN-001'));
    }
}
