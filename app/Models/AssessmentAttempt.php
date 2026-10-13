<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentAttempt extends Model
{
    protected $fillable = ['assessment_id', 'enrollment_id', 'attempt_number', 'score', 'passed', 'started_at', 'expires_at', 'submitted_at'];

    protected function casts(): array
    {
        return ['score' => 'decimal:2', 'passed' => 'boolean', 'started_at' => 'datetime', 'expires_at' => 'datetime', 'submitted_at' => 'datetime'];
    }

    public function responses(): HasMany
    {
        return $this->hasMany(AssessmentResponse::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
