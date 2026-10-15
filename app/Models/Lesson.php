<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $module_id
 * @property string $title
 * @property string $type
 * @property string|null $content
 * @property string|null $transcript
 * @property string|null $resource_url
 * @property bool $is_required
 * @property int $sequence
 */
class Lesson extends Model
{
    protected $fillable = ['module_id', 'title', 'type', 'content', 'transcript', 'resource_url', 'is_required', 'sequence'];

    protected function casts(): array
    {
        return ['is_required' => 'boolean'];
    }

    /** @return BelongsTo<CourseModule, $this> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    /** @return HasMany<LessonResource, $this> */
    public function resources(): HasMany
    {
        return $this->hasMany(LessonResource::class);
    }
}
