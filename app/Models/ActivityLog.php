<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property array<string, mixed>|null $properties
 * @property Carbon $created_at
 */
class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['actor_id', 'event', 'subject_type', 'subject_id', 'properties'];

    protected function casts(): array
    {
        return ['properties' => 'array', 'created_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /** @param array<string, mixed> $properties */
    public static function record(User $actor, string $event, ?Model $subject = null, array $properties = []): self
    {
        return static::query()->create([
            'actor_id' => $actor->id,
            'event' => $event,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
        ]);
    }
}
