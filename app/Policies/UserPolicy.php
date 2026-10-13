<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $actor, string $ability): ?bool
    {
        return $actor->hasRole('super-admin') ? true : null;
    }

    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('manage-users');
    }

    public function view(User $actor, User $user): bool
    {
        return $actor->hasPermission('manage-users')
            || ($actor->hasPermission('view-team') && $user->manager_id === $actor->id);
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission('manage-users');
    }

    public function update(User $actor, User $user): bool
    {
        return $actor->hasPermission('manage-users') && ! $user->hasRole('super-admin');
    }
}
