<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehiclePartOrderResource extends JsonResource
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
            'price' => $this->price,
            'delivery_cost' => $this->delivery_cost,
            'supplier_part' => new SupplierPartResource($this->whenLoaded('supplierPart')),
            'created_at' => $this->created_at,
        ];
    }
}
