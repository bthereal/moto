<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Vehicle::class);

        return VehicleResource::collection(
            Vehicle::with(['team', 'driver'])->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        Gate::authorize('create', Vehicle::class);

        $vehicle = Vehicle::create($request->validated());

        return VehicleResource::make($vehicle->load(['team', 'driver']))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle): VehicleResource
    {
        Gate::authorize('view', $vehicle);

        return new VehicleResource($vehicle->load(['team', 'driver', 'vehicleParts.part', 'vehicleParts.order.supplierPart.supplier']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): VehicleResource
    {
        Gate::authorize('update', $vehicle);

        $vehicle->update($request->validated());

        return new VehicleResource($vehicle->load(['team', 'driver']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle): Response
    {
        Gate::authorize('delete', $vehicle);

        $vehicle->delete();

        return response()->noContent();
    }
}
