<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $assessment_id
 * @property int $enrollment_id
 * @property int $attempt_number
 * @property float|null $score
 * @property bool|null $passed
 * @property Carbon|null $started_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $submitted_at
 */
class AssessmentAttempt extends Model
{
    protected $fillable = ['assessment_id', 'enrollment_id', 'attempt_number', 'score', 'passed', 'started_at', 'expires_at', 'submitted_at'];

    protected function casts(): array
    {
        return ['score' => 'decimal:2', 'passed' => 'boolean', 'started_at' => 'datetime', 'expires_at' => 'datetime', 'submitted_at' => 'datetime'];
    }

    /** @return HasMany<AssessmentResponse, $this> */
    public function responses(): HasMany
    {
        return $this->hasMany(AssessmentResponse::class);
    }

    /** @return BelongsTo<Assessment, $this> */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
