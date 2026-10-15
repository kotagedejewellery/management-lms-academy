<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property array<string, mixed> $filters
 * @property string $status
 * @property string|null $path
 * @property string|null $error
 * @property Carbon|null $completed_at
 * @property Carbon $created_at
 */
class ReportExport extends Model
{
    protected $fillable = ['user_id', 'filters', 'status', 'path', 'error', 'completed_at'];

    protected function casts(): array
    {
        return ['filters' => 'array', 'completed_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
