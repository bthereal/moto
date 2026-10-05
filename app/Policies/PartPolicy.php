<?php

namespace App\Policies;

use App\Models\Part;
use App\Models\User;

class PartPolicy
{
    /**
     * Any authenticated user may browse part records.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user may read a single part record.
     */
    public function view(User $user, Part $part): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Part $part): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Part $part): bool
    {
        return $user->isAdmin();
    }
}
