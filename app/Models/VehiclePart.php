<?php

namespace App\Models;

use App\Enums\VehiclePartStatus;
use Database\Factories\VehiclePartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use LogicException;

#[Fillable(['vehicle_id', 'part_id', 'status'])]
class VehiclePart extends Model
{
    /** @use HasFactory<VehiclePartFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => VehiclePartStatus::class,
        ];
    }

    /**
     * The vehicle this part requirement belongs to.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * The part being required, sourced, or fitted.
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * The order placed to fulfil this requirement, if any.
     */
    public function order(): HasOne
    {
        return $this->hasOne(VehiclePartOrder::class);
    }

    /**
     * Fulfil this requirement from a supplier's stock: snapshot the price and
     * delivery cost onto an order, decrement the supplier's stock, and move
     * this requirement into the ordered stage.
     *
     * Callers are expected to have already validated that this requirement is
     * still Required and that the supplier part has stock — this guard exists
     * so the method can never be used to produce an inconsistent state.
     */
    public function placeOrder(SupplierPart $supplierPart): VehiclePartOrder
    {
        if ($this->status !== VehiclePartStatus::Required) {
            throw new LogicException('Only a required part can be ordered.');
        }

        if ($supplierPart->part_id !== $this->part_id) {
            throw new LogicException('That listing does not stock the required part.');
        }

        if (! $supplierPart->inStock()) {
            throw new LogicException('The selected supplier has no stock of this part.');
        }

        return DB::transaction(function () use ($supplierPart) {
            $order = $this->order()->create([
                'supplier_part_id' => $supplierPart->id,
                'price' => $supplierPart->price,
                'delivery_cost' => $supplierPart->delivery_cost,
            ]);

            $supplierPart->decrement('quantity');

            $this->update(['status' => VehiclePartStatus::Ordered]);

            return $order;
        });
    }
}
