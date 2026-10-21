<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $assessment_id
 * @property int $enrollment_id
 * @property int $authorized_by
 * @property Carbon|null $used_at
 * @property Carbon|null $cancelled_at
 */
class AssessmentRetakeAuthorization extends Model
{
    protected $fillable = ['assessment_id', 'enrollment_id', 'authorized_by', 'used_at', 'cancelled_at'];

    protected function casts(): array
    {
        return ['used_at' => 'datetime', 'cancelled_at' => 'datetime'];
    }

    /** @return BelongsTo<Assessment, $this> */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    /** @return BelongsTo<Enrollment, $this> */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /** @return BelongsTo<User, $this> */
    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }
}
