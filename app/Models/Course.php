<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property int $category_id
 * @property int|null $division_id
 * @property int|null $position_id
 * @property int|null $prerequisite_course_id
 * @property string $code
 * @property string $title
 * @property string|null $description
 * @property int|null $estimated_minutes
 * @property bool $is_mandatory
 * @property bool $requires_post_test
 * @property bool $certificate_enabled
 * @property int $passing_grade
 * @property string $status
 * @property Carbon|null $published_at
 * @property int|null $is_assigned
 */
class Course extends Model
{
    protected $fillable = ['category_id', 'division_id', 'position_id', 'prerequisite_course_id', 'code', 'title', 'description', 'thumbnail_path', 'thumbnail_disk', 'thumbnail_mime', 'estimated_minutes', 'is_mandatory', 'requires_post_test', 'certificate_enabled', 'passing_grade', 'status', 'published_at'];

    protected function casts(): array
    {
        return ['is_mandatory' => 'boolean', 'requires_post_test' => 'boolean', 'certificate_enabled' => 'boolean', 'published_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('dashboard:management'));
        static::deleted(fn () => Cache::forget('dashboard:management'));
    }

    /** @return BelongsTo<CourseCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    /** @return BelongsTo<Division, $this> */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /** @return BelongsTo<Position, $this> */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /** @return BelongsTo<Course, $this> */
    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(self::class, 'prerequisite_course_id');
    }

    /** @return HasMany<CourseModule, $this> */
    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('sequence');
    }

    /** @return HasManyThrough<Lesson, CourseModule, $this> */
    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, CourseModule::class, 'course_id', 'module_id');
    }

    /** @return HasMany<Enrollment, $this> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /** @return HasMany<Assessment, $this> */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function trainers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_trainers')->withTimestamps();
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
