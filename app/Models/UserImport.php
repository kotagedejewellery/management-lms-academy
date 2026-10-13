<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserImport extends Model
{
    protected $fillable = ['user_id', 'path', 'original_name', 'status', 'total_rows', 'imported_rows', 'skipped_rows', 'errors', 'completed_at'];

    protected function casts(): array
    {
        return ['errors' => 'array', 'completed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
