<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    /**
     * Any authenticated user may browse vehicle records.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user may read a single vehicle record.
     */
    public function view(User $user, Vehicle $vehicle): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->isAdmin();
    }
}
