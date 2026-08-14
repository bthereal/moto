<?php

namespace App\Models;

use App\Enums\VehiclePartStatus;
use Database\Factories\VehiclePartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
