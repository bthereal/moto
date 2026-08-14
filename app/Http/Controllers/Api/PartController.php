<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePartRequest;
use App\Http\Requests\UpdatePartRequest;
use App\Http\Resources\PartResource;
use App\Models\Part;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return PartResource::collection(
            Part::orderBy('category')->orderBy('name')->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePartRequest $request): JsonResponse
    {
        $part = Part::create($request->validated());

        return PartResource::make($part)->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Part $part): PartResource
    {
        return new PartResource($part);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePartRequest $request, Part $part): PartResource
    {
        $part->update($request->validated());

        return new PartResource($part);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Part $part): Response
    {
        $part->delete();

        return response()->noContent();
    }
}
