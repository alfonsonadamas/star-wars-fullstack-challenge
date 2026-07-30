<?php

namespace Tests\Feature\Feature\Api;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SwapiApiTest extends TestCase
{
    public function test_it_returns_normalized_films(): void
    {
        Http::fake([
            'https://swapi.info/api/films' => Http::response([
                [
                    'title' => 'A New Hope',
                    'episode_id' => 4,
                    'director' => 'George Lucas',
                    'release_date' => '1977-05-25',
                    'starships' => [
                        'https://swapi.info/api/starships/2',
                        'https://swapi.info/api/starships/10',
                    ],
                    'url' => 'https://swapi.info/api/films/1',
                ],
            ]),
        ]);

        $this->getJson('/api/swapi/films')
            ->assertOk()
            ->assertJsonPath('data.0.id', 1)
            ->assertJsonPath('data.0.title', 'A New Hope')
            ->assertJsonPath('data.0.starships_count', 2);

        Http::assertSentCount(1);
    }

    public function test_it_returns_a_normalized_starship(): void
    {
        Http::fake([
            'https://swapi.info/api/starships/10' => Http::response([
                'name' => 'Millennium Falcon',
                'model' => 'YT-1300 light freighter',
                'manufacturer' => 'Corellian Engineering',
                'crew' => '4',
                'max_atmosphering_speed' => '1050',
                'cargo_capacity' => '100000',
                'url' => 'https://swapi.info/api/starships/10',
            ]),
        ]);

        $this->getJson('/api/swapi/starships/10')
            ->assertOk()
            ->assertJsonPath('data.id', 10)
            ->assertJsonPath('data.name', 'Millennium Falcon');
    }
}
