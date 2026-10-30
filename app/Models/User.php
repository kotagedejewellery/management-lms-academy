<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $name
 * @property string|null $employee_id
 * @property Carbon|null $hired_at
 * @property string $email
 * @property string|null $phone
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $division_id
 * @property int|null $position_id
 * @property int|null $branch_id
 * @property int|null $manager_id
 * @property string $status
 */
#[Fillable(['employee_id', 'hired_at', 'name', 'email', 'phone', 'password', 'division_id', 'position_id', 'branch_id', 'manager_id', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'hired_at' => 'date',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('dashboard:operations'));
        static::deleted(fn () => Cache::forget('dashboard:operations'));
    }

    /** @return BelongsTo<Division, $this> */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /** @return BelongsTo<Position, $this> */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** @return BelongsTo<User, $this> */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    /** @return HasMany<User, $this> */
    public function directReports(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    /** @return HasMany<Enrollment, $this> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /** @return HasMany<Certificate, $this> */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /** @return BelongsToMany<Course, $this> */
    public function managedCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_trainers')->withTimestamps();
    }

    /** @return HasMany<ReportExport, $this> */
    public function reportExports(): HasMany
    {
        return $this->hasMany(ReportExport::class);
    }

    /** @return HasMany<LearningPathAssignment, $this> */
    public function learningPathAssignments(): HasMany
    {
        return $this->hasMany(LearningPathAssignment::class);
    }

    /** @return HasMany<UserNotification, $this> */
    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    /** @return HasMany<Announcement, $this> */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    /** @return HasMany<UserImport, $this> */
    public function userImports(): HasMany
    {
        return $this->hasMany(UserImport::class);
    }

    /** @return BelongsToMany<Role, $this> */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasRole(string $role): bool
    {
        return $this->relationLoaded('roles')
            ? $this->roles->contains('slug', $role)
            : $this->roles()->where('slug', $role)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        return $this->roles()->whereHas('permissions', fn ($query) => $query->where('slug', $permission))->exists();
    }

    public function canManageCourse(Course $course): bool
    {
        if (! $this->hasPermission('manage-courses')) {
            return false;
        }

        return $this->hasPermission('manage-enrollments')
            || $this->managedCourses()->whereKey($course)->exists();
    }
}
