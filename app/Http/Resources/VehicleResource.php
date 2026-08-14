<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chassis' => $this->chassis,
            'engine_supplier' => $this->engine_supplier,
            'car_number' => $this->car_number,
            'season_year' => $this->season_year,
            'status' => $this->status,
            'team' => new TeamResource($this->whenLoaded('team')),
            'driver' => new UserResource($this->whenLoaded('driver')),
            'parts' => VehiclePartResource::collection($this->whenLoaded('vehicleParts')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
