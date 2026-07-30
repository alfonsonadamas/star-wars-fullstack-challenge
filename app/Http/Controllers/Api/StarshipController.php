<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStarshipRequest;
use App\Http\Requests\UpdateStarshipRequest;
use App\Http\Resources\StarshipResource;
use App\Models\Starship;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class StarshipController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return StarshipResource::collection(
            Starship::query()
                ->when(
                    $request->filled('swapi_id'),
                    fn ($query) => $query->where('swapi_id', $request->integer('swapi_id')),
                )
                ->latest()
                ->paginate(12),
        );
    }

    public function store(StoreStarshipRequest $request): StarshipResource
    {
        $starship = Starship::query()->create($request->validated());

        return new StarshipResource($starship);
    }

    public function show(Starship $starship): StarshipResource
    {
        return new StarshipResource($starship);
    }

    public function update(UpdateStarshipRequest $request, Starship $starship): StarshipResource
    {
        $starship->update($request->validated());

        return new StarshipResource($starship->refresh());
    }

    public function destroy(Starship $starship): Response
    {
        $starship->delete();

        return response()->noContent();
    }
}
