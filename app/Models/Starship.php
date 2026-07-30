<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Starship extends Model
{
    /** @use HasFactory<\Database\Factories\StarshipFactory> */
    use HasFactory;

    protected $fillable = [
        'swapi_id',
        'name',
        'max_atmosphering_speed',
        'cargo_capacity',
    ];

    protected function casts(): array
    {
        return [
            'swapi_id' => 'integer',
            'max_atmosphering_speed' => 'integer',
            'cargo_capacity' => 'integer',
        ];
    }
}
