<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Team::class);

        return TeamResource::collection(
            Team::withCount(['users', 'vehicles'])->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamRequest $request): JsonResponse
    {
        Gate::authorize('create', Team::class);

        $team = Team::create($request->validated());

        return TeamResource::make($team)->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team): TeamResource
    {
        Gate::authorize('view', $team);

        return new TeamResource($team->load('users', 'vehicles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamRequest $request, Team $team): TeamResource
    {
        Gate::authorize('update', $team);

        $team->update($request->validated());

        return new TeamResource($team);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team): Response
    {
        Gate::authorize('delete', $team);

        $team->delete();

        return response()->noContent();
    }
}
