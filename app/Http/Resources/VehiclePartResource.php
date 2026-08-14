<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehiclePartResource extends JsonResource
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
            'vehicle_id' => $this->vehicle_id,
            'status' => $this->status,
            'part' => new PartResource($this->whenLoaded('part')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
