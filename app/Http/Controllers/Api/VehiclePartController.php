<?php

namespace App\Http\Controllers\Api;

use App\Enums\VehiclePartStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderVehiclePartRequest;
use App\Http\Requests\StoreVehiclePartRequest;
use App\Http\Requests\UpdateVehiclePartRequest;
use App\Http\Resources\SupplierPartResource;
use App\Http\Resources\VehiclePartResource;
use App\Models\SupplierPart;
use App\Models\Vehicle;
use App\Models\VehiclePart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VehiclePartController extends Controller
{
    /**
     * Require a part for the given vehicle. Only permitted while the vehicle is in testing.
     */
    public function store(StoreVehiclePartRequest $request, Vehicle $vehicle): JsonResponse
    {
        Gate::authorize('create', [VehiclePart::class, $vehicle]);

        $vehiclePart = $vehicle->vehicleParts()->create([
            'part_id' => $request->validated('part_id'),
            'status' => VehiclePartStatus::Required,
        ]);

        return VehiclePartResource::make($vehiclePart->load('part'))->response()->setStatusCode(201);
    }

    /**
     * Advance (or otherwise change) the status of a part requirement.
     */
    public function update(UpdateVehiclePartRequest $request, Vehicle $vehicle, VehiclePart $vehiclePart): VehiclePartResource
    {
        $this->ensureBelongsToVehicle($vehicle, $vehiclePart);
        Gate::authorize('update', $vehiclePart);

        $vehiclePart->update($request->validated());

        return new VehiclePartResource($vehiclePart->load('part'));
    }

    /**
     * Remove a part requirement from a vehicle.
     */
    public function destroy(Vehicle $vehicle, VehiclePart $vehiclePart): Response
    {
        $this->ensureBelongsToVehicle($vehicle, $vehiclePart);
        Gate::authorize('delete', $vehiclePart);

        $vehiclePart->delete();

        return response()->noContent();
    }

    /**
     * List supplier listings that currently stock the required part.
     */
    public function availableSuppliers(Vehicle $vehicle, VehiclePart $vehiclePart): AnonymousResourceCollection
    {
        $this->ensureBelongsToVehicle($vehicle, $vehiclePart);
        Gate::authorize('view', $vehiclePart);

        return SupplierPartResource::collection(
            SupplierPart::where('part_id', $vehiclePart->part_id)
                ->where('quantity', '>', 0)
                ->with('supplier')
                ->orderBy('price')
                ->get()
        );
    }

    /**
     * Fulfil a required part from a chosen supplier listing.
     */
    public function order(OrderVehiclePartRequest $request, Vehicle $vehicle, VehiclePart $vehiclePart): VehiclePartResource
    {
        $this->ensureBelongsToVehicle($vehicle, $vehiclePart);
        Gate::authorize('order', $vehiclePart);

        $supplierPart = SupplierPart::findOrFail($request->validated('supplier_part_id'));

        $vehiclePart->placeOrder($supplierPart);

        return new VehiclePartResource($vehiclePart->load(['part', 'order.supplierPart.supplier']));
    }

    private function ensureBelongsToVehicle(Vehicle $vehicle, VehiclePart $vehiclePart): void
    {
        if ($vehiclePart->vehicle_id !== $vehicle->id) {
            throw new NotFoundHttpException;
        }
    }
}
