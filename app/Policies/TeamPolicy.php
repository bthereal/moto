<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Any authenticated user may browse team records.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user may read a single team record.
     */
    public function view(User $user, Team $team): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Team $team): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Team $team): bool
    {
        return $user->isAdmin();
    }
}
