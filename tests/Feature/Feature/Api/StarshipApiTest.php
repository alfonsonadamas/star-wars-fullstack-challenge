<?php

namespace Tests\Feature\Feature\Api;

use App\Models\Starship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StarshipApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_starship(): void
    {
        $payload = [
            'swapi_id' => 10,
            'name' => 'Millennium Falcon',
            'max_atmosphering_speed' => 1050,
            'cargo_capacity' => 100000,
        ];

        $this->postJson('/api/starships', $payload)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Millennium Falcon');

        $this->assertDatabaseHas('starships', $payload);
    }

    public function test_it_lists_and_shows_starships(): void
    {
        $starship = Starship::factory()->create();

        $this->getJson('/api/starships')
            ->assertOk()
            ->assertJsonPath('data.0.id', $starship->id);

        $this->getJson("/api/starships/{$starship->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $starship->id);
    }

    public function test_it_filters_starships_by_swapi_id(): void
    {
        $expected = Starship::factory()->create(['swapi_id' => 10]);
        Starship::factory()->create(['swapi_id' => 2]);

        $this->getJson('/api/starships?swapi_id=10')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_it_does_not_create_the_same_swapi_starship_twice(): void
    {
        Starship::factory()->create(['swapi_id' => 10]);

        $this->postJson('/api/starships', [
            'swapi_id' => 10,
            'name' => 'Millennium Falcon',
            'max_atmosphering_speed' => 1050,
            'cargo_capacity' => 100000,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('swapi_id');
    }

    public function test_it_updates_a_starship(): void
    {
        $starship = Starship::factory()->create();

        $this->patchJson("/api/starships/{$starship->id}", [
            'name' => 'Updated Falcon',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Falcon');
    }

    public function test_it_deletes_a_starship(): void
    {
        $starship = Starship::factory()->create();

        $this->deleteJson("/api/starships/{$starship->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('starships', ['id' => $starship->id]);
    }

    public function test_it_validates_required_fields(): void
    {
        $this->postJson('/api/starships', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'max_atmosphering_speed',
                'cargo_capacity',
            ]);
    }
}
