<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Cache;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'division_id', 'position_id', 'prerequisite_course_id', 'code', 'title', 'description', 'estimated_minutes', 'is_mandatory', 'requires_post_test', 'passing_grade', 'status', 'published_at'];

    protected function casts(): array
    {
        return ['is_mandatory' => 'boolean', 'requires_post_test' => 'boolean', 'published_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('dashboard:management'));
        static::deleted(fn () => Cache::forget('dashboard:management'));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(self::class, 'prerequisite_course_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('sequence');
    }

    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, CourseModule::class, 'course_id', 'module_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function prerequisiteCompletedBy(User $user): bool
    {
        if ($this->prerequisite_course_id && ! $user->enrollments()
            ->where('course_id', $this->prerequisite_course_id)
            ->where('status', 'completed')
            ->exists()) {
            return false;
        }

        $previousCourseIds = LearningPathCourse::query()
            ->from('learning_path_courses as current_path_course')
            ->join('learning_path_assignments', 'learning_path_assignments.learning_path_id', '=', 'current_path_course.learning_path_id')
            ->join('learning_paths', 'learning_paths.id', '=', 'current_path_course.learning_path_id')
            ->join('learning_path_courses as previous_path_course', function ($join): void {
                $join->on('previous_path_course.learning_path_id', '=', 'current_path_course.learning_path_id')
                    ->whereColumn('previous_path_course.sequence', '<', 'current_path_course.sequence');
            })
            ->join('courses as previous_courses', 'previous_courses.id', '=', 'previous_path_course.course_id')
            ->where('learning_path_assignments.user_id', $user->id)
            ->where('learning_paths.status', 'published')
            ->where('current_path_course.course_id', $this->id)
            ->where('previous_courses.is_mandatory', true)
            ->pluck('previous_path_course.course_id')
            ->unique();

        return $previousCourseIds->isEmpty() || $user->enrollments()
            ->whereIn('course_id', $previousCourseIds)
            ->where('status', 'completed')
            ->count() === $previousCourseIds->count();
    }
}
