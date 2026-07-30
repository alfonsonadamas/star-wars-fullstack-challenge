<?php

use App\Http\Controllers\Api\StarshipController;
use App\Http\Controllers\Api\SwapiController;
use Illuminate\Support\Facades\Route;

Route::prefix('swapi')->group(function (): void {
    Route::get('/films', [SwapiController::class, 'films']);
    Route::get('/films/{film}/starships', [SwapiController::class, 'filmStarships'])
        ->whereNumber('film');
    Route::get('/starships/{starship}', [SwapiController::class, 'starship'])
        ->whereNumber('starship');
});

Route::apiResource('starships', StarshipController::class);
