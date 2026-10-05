<?php

namespace App\Models;

use App\Enums\PartCategory;
use Database\Factories\PartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'part_number', 'category', 'manufacturer', 'description'])]
class Part extends Model
{
    /** @use HasFactory<PartFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'category' => PartCategory::class,
        ];
    }

    /**
     * Every vehicle association (past and present) for this part.
     */
    public function vehicleParts(): HasMany
    {
        return $this->hasMany(VehiclePart::class);
    }

    /**
     * Supplier stock listings for this part.
     */
    public function supplierParts(): HasMany
    {
        return $this->hasMany(SupplierPart::class);
    }
}
