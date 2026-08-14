<?php

namespace App\Models;

use App\Enums\VehiclePartStatus;
use App\Enums\VehicleStatus;
use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['team_id', 'driver_id', 'chassis', 'engine_supplier', 'car_number', 'season_year', 'status'])]
class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'car_number' => 'integer',
            'season_year' => 'integer',
            'status' => VehicleStatus::class,
        ];
    }

    /**
     * The team that entered this vehicle.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * The driver currently assigned to this vehicle, if any.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Every part requirement (past and present) raised against this vehicle.
     */
    public function vehicleParts(): HasMany
    {
        return $this->hasMany(VehiclePart::class);
    }

    /**
     * Parts required, in transit, delivered, or fitted for this vehicle.
     */
    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class)
            ->using(VehiclePart::class)
            ->withPivot('id', 'status')
            ->withTimestamps();
    }

    /**
     * Whether this vehicle has any part requirement not yet fitted.
     */
    public function hasOutstandingParts(): bool
    {
        return $this->vehicleParts()
            ->where('status', '!=', VehiclePartStatus::Fitted)
            ->exists();
    }

    /**
     * Whether this vehicle is eligible to move from testing back to active.
     */
    public function canActivate(): bool
    {
        return ! $this->hasOutstandingParts();
    }
}
