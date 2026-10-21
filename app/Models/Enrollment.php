<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property int $user_id
 * @property int $course_id
 * @property string $status
 * @property Carbon|null $due_at
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property-read int $completed_lessons_count
 * @property-read int $lessons_count
 * @property-read float|null $pre_test_score
 * @property-read float|null $best_post_score
 * @property-read int $post_test_attempts
 */
class Enrollment extends Model
{
    protected $fillable = ['user_id', 'course_id', 'status', 'due_at', 'started_at', 'completed_at'];

    protected function casts(): array
    {
        return ['due_at' => 'datetime', 'started_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('dashboard:management'));
        static::deleted(fn () => Cache::forget('dashboard:management'));
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return HasMany<LessonProgress, $this> */
    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /** @return HasMany<Certificate, $this> */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /** @return HasMany<AssessmentRetakeAuthorization, $this> */
    public function retakeAuthorizations(): HasMany
    {
        return $this->hasMany(AssessmentRetakeAuthorization::class);
    }
}
