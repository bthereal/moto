<?php

namespace App\Models;

use Database\Factories\VehiclePartOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vehicle_part_id', 'supplier_part_id', 'price', 'delivery_cost'])]
class VehiclePartOrder extends Model
{
    /** @use HasFactory<VehiclePartOrderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'delivery_cost' => 'decimal:2',
        ];
    }

    /**
     * The vehicle part requirement this order fulfils.
     */
    public function vehiclePart(): BelongsTo
    {
        return $this->belongsTo(VehiclePart::class);
    }

    /**
     * The supplier listing this order was placed against, captured at order
     * time. Nullable because the listing may later be removed by the supplier
     * without erasing this order's history.
     */
    public function supplierPart(): BelongsTo
    {
        return $this->belongsTo(SupplierPart::class);
    }
}
