<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $course_id
 * @property int|null $module_id
 * @property string $type
 * @property bool $is_required
 * @property int $passing_grade
 * @property int $attempt_limit
 * @property int|null $time_limit_minutes
 * @property-read int $questions_count
 */
class Assessment extends Model
{
    protected $fillable = ['course_id', 'module_id', 'type', 'is_required', 'passing_grade', 'attempt_limit', 'time_limit_minutes'];

    protected function casts(): array
    {
        return ['is_required' => 'boolean'];
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return BelongsTo<CourseModule, $this> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    /** @return HasMany<Question, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sequence');
    }

    /** @return HasMany<AssessmentAttempt, $this> */
    public function attempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    /** @return HasMany<AssessmentRetakeAuthorization, $this> */
    public function retakeAuthorizations(): HasMany
    {
        return $this->hasMany(AssessmentRetakeAuthorization::class);
    }
}
