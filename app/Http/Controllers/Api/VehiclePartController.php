<?php

namespace App\Http\Controllers\Api;

use App\Enums\VehiclePartStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehiclePartRequest;
use App\Http\Requests\UpdateVehiclePartRequest;
use App\Http\Resources\VehiclePartResource;
use App\Models\Vehicle;
use App\Models\VehiclePart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VehiclePartController extends Controller
{
    /**
     * Require a part for the given vehicle. Only permitted while the vehicle is in testing.
     */
    public function store(StoreVehiclePartRequest $request, Vehicle $vehicle): JsonResponse
    {
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

        $vehiclePart->update($request->validated());

        return new VehiclePartResource($vehiclePart->load('part'));
    }

    /**
     * Remove a part requirement from a vehicle.
     */
    public function destroy(Vehicle $vehicle, VehiclePart $vehiclePart): Response
    {
        $this->ensureBelongsToVehicle($vehicle, $vehiclePart);

        $vehiclePart->delete();

        return response()->noContent();
    }

    private function ensureBelongsToVehicle(Vehicle $vehicle, VehiclePart $vehiclePart): void
    {
        if ($vehiclePart->vehicle_id !== $vehicle->id) {
            throw new NotFoundHttpException;
        }
    }
}
