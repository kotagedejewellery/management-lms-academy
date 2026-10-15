<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $path
 * @property string $original_name
 * @property string $status
 * @property int $total_rows
 * @property int $imported_rows
 * @property int $skipped_rows
 * @property array<int, array{line: int, message: string}>|null $errors
 * @property Carbon|null $completed_at
 * @property Carbon $created_at
 */
class UserImport extends Model
{
    protected $fillable = ['user_id', 'path', 'original_name', 'status', 'total_rows', 'imported_rows', 'skipped_rows', 'errors', 'completed_at'];

    protected function casts(): array
    {
        return ['errors' => 'array', 'completed_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
