<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Any authenticated user may browse the roster.
     */
    public function viewAny(User $actor): bool
    {
        return true;
    }

    /**
     * Any authenticated user may read a single roster entry.
     */
    public function view(User $actor, User $user): bool
    {
        return true;
    }

    /**
     * Only admins may create accounts — this ability also gates assigning a role.
     */
    public function create(User $actor): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Only admins may change an account, including its role. Self-service
     * profile and password changes go through the web (Breeze) routes, which
     * cannot alter the role column.
     */
    public function update(User $actor, User $user): bool
    {
        return $actor->isAdmin();
    }

    public function delete(User $actor, User $user): bool
    {
        return $actor->isAdmin();
    }
}
