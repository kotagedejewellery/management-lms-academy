<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPath extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'status', 'published_at'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function pathCourses(): HasMany
    {
        return $this->hasMany(LearningPathCourse::class)->orderBy('sequence');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LearningPathAssignment::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(LearningPathRule::class);
    }
}
