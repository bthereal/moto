<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehiclePart;

class VehiclePartPolicy
{
    /**
     * Any authenticated user may read a vehicle's part requirements and see
     * which suppliers stock them.
     */
    public function view(User $user, VehiclePart $vehiclePart): bool
    {
        return true;
    }

    /**
     * Only admins may add a part requirement to a vehicle.
     */
    public function create(User $user, Vehicle $vehicle): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, VehiclePart $vehiclePart): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, VehiclePart $vehiclePart): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only admins may fulfil a requirement from a supplier listing, since this
     * commits a purchase and decrements supplier stock.
     */
    public function order(User $user, VehiclePart $vehiclePart): bool
    {
        return $user->isAdmin();
    }
}
