<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Enrollment extends Model
{
    use HasFactory;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }
}
