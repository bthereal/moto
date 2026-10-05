<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierPartResource extends JsonResource
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
            'quantity' => $this->quantity,
            'price' => $this->price,
            'location' => $this->location,
            'delivery_cost' => $this->delivery_cost,
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'part' => new PartResource($this->whenLoaded('part')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
