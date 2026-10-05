<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Supplier::class);

        return SupplierResource::collection(
            Supplier::withCount('supplierParts')->orderBy('name')->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        Gate::authorize('create', Supplier::class);

        $supplier = Supplier::create($request->validated());

        return SupplierResource::make($supplier)->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier): SupplierResource
    {
        Gate::authorize('view', $supplier);

        return new SupplierResource($supplier->load('supplierParts.part'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): SupplierResource
    {
        Gate::authorize('update', $supplier);

        $supplier->update($request->validated());

        return new SupplierResource($supplier);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier): Response
    {
        Gate::authorize('delete', $supplier);

        $supplier->delete();

        return response()->noContent();
    }
}
