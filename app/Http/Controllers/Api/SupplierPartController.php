<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierPartRequest;
use App\Http\Requests\UpdateSupplierPartRequest;
use App\Http\Resources\SupplierPartResource;
use App\Models\Supplier;
use App\Models\SupplierPart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SupplierPartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Supplier $supplier): AnonymousResourceCollection
    {
        return SupplierPartResource::collection(
            $supplier->supplierParts()->with('part')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierPartRequest $request, Supplier $supplier): JsonResponse
    {
        $supplierPart = $supplier->supplierParts()->create($request->validated());

        return SupplierPartResource::make($supplierPart->load('part'))->response()->setStatusCode(201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierPartRequest $request, Supplier $supplier, SupplierPart $supplierPart): SupplierPartResource
    {
        $this->ensureBelongsToSupplier($supplier, $supplierPart);

        $supplierPart->update($request->validated());

        return new SupplierPartResource($supplierPart->load('part'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier, SupplierPart $supplierPart): Response
    {
        $this->ensureBelongsToSupplier($supplier, $supplierPart);

        $supplierPart->delete();

        return response()->noContent();
    }

    private function ensureBelongsToSupplier(Supplier $supplier, SupplierPart $supplierPart): void
    {
        if ($supplierPart->supplier_id !== $supplier->id) {
            throw new NotFoundHttpException;
        }
    }
}
