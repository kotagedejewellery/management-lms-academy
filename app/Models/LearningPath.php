<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPath extends Model
{
    protected $fillable = ['name', 'description', 'status', 'published_at'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    /** @return HasMany<LearningPathCourse, $this> */
    public function pathCourses(): HasMany
    {
        return $this->hasMany(LearningPathCourse::class)->orderBy('sequence');
    }

    /** @return HasMany<LearningPathAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(LearningPathAssignment::class);
    }

    /** @return HasMany<LearningPathRule, $this> */
    public function rules(): HasMany
    {
        return $this->hasMany(LearningPathRule::class);
    }
}
