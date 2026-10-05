<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\SupplierPart;
use App\Models\User;

class SupplierPartPolicy
{
    /**
     * Any authenticated user may browse a supplier's catalogue.
     */
    public function viewAny(User $user, Supplier $supplier): bool
    {
        return true;
    }

    /**
     * Only admins may add a listing to a supplier's catalogue.
     */
    public function create(User $user, Supplier $supplier): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, SupplierPart $supplierPart): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, SupplierPart $supplierPart): bool
    {
        return $user->isAdmin();
    }
}
