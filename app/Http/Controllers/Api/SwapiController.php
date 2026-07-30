<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SwapiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;

class SwapiController extends Controller
{
    public function __construct(private readonly SwapiService $swapi) {}

    public function films(): JsonResponse
    {
        return $this->respond(fn (): array => $this->swapi->films());
    }

    public function filmStarships(int $film): JsonResponse
    {
        return $this->respond(fn (): array => $this->swapi->filmStarships($film));
    }

    public function starship(int $starship): JsonResponse
    {
        return $this->respond(fn (): array => $this->swapi->starship($starship));
    }

    private function respond(callable $callback): JsonResponse
    {
        try {
            return response()->json(['data' => $callback()]);
        } catch (ConnectionException|RequestException $exception) {
            report($exception);

            return response()->json([
                'message' => 'No fue posible consultar la información de Star Wars.',
            ], 502);
        }
    }
}
