<?php

namespace App\Models;

use Database\Factories\SupplierPartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['supplier_id', 'part_id', 'quantity', 'price', 'location', 'delivery_cost'])]
class SupplierPart extends Model
{
    /** @use HasFactory<SupplierPartFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price' => 'decimal:2',
            'delivery_cost' => 'decimal:2',
        ];
    }

    /**
     * The supplier stocking this part.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * The part being stocked.
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * Orders placed against this listing.
     */
    public function vehiclePartOrders(): HasMany
    {
        return $this->hasMany(VehiclePartOrder::class);
    }

    /**
     * Whether this listing currently has stock available to order.
     */
    public function inStock(): bool
    {
        return $this->quantity > 0;
    }
}
