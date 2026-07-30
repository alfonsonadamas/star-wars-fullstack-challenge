<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SwapiService
{
    public function films(): array
    {
        return Cache::remember(
            'swapi.films',
            $this->cacheTtl(),
            function (): array {
                $films = $this->get('films');

                return collect($this->items($films))
                    ->map(fn (array $film): array => $this->normalizeFilm($film))
                    ->sortBy('episode_id')
                    ->values()
                    ->all();
            },
        );
    }

    public function filmStarships(int $filmId): array
    {
        return Cache::remember(
            "swapi.films.{$filmId}.starships",
            $this->cacheTtl(),
            function () use ($filmId): array {
                $film = $this->get("films/{$filmId}");
                $urls = Arr::wrap($film['starships'] ?? []);

                $responses = Http::pool(fn (Pool $pool): array => collect($urls)
                    ->mapWithKeys(function (string $url) use ($pool): array {
                        $id = $this->resourceId($url);

                        return [
                            (string) $id => $pool
                                ->as((string) $id)
                                ->acceptJson()
                                ->timeout(10)
                                ->get($url),
                        ];
                    })
                    ->values()
                    ->all());

                $starships = collect($responses)
                    ->filter(fn ($response): bool => $response->successful())
                    ->map(fn ($response): array => $this->normalizeStarship($response->json()))
                    ->values()
                    ->all();

                return [
                    'film' => $this->normalizeFilm($film),
                    'starships' => $starships,
                ];
            },
        );
    }

    public function starship(int $starshipId): array
    {
        return Cache::remember(
            "swapi.starships.{$starshipId}",
            $this->cacheTtl(),
            fn (): array => $this->normalizeStarship($this->get("starships/{$starshipId}")),
        );
    }

    private function get(string $path): array
    {
        return Http::baseUrl(rtrim(config('services.swapi.base_url'), '/'))
            ->acceptJson()
            ->timeout(10)
            ->retry(2, 250)
            ->get(ltrim($path, '/'))
            ->throw()
            ->json();
    }

    private function items(array $response): array
    {
        return $response['results'] ?? $response;
    }

    private function normalizeFilm(array $film): array
    {
        return [
            'id' => $this->resourceId($film['url'] ?? ''),
            'title' => $film['title'],
            'episode_id' => (int) $film['episode_id'],
            'director' => $film['director'],
            'release_date' => $film['release_date'],
            'starships_count' => count(Arr::wrap($film['starships'] ?? [])),
        ];
    }

    private function normalizeStarship(array $starship): array
    {
        return [
            'id' => $this->resourceId($starship['url'] ?? ''),
            'name' => $starship['name'],
            'model' => $starship['model'],
            'manufacturer' => $starship['manufacturer'],
            'crew' => $starship['crew'],
            'max_atmosphering_speed' => $starship['max_atmosphering_speed'],
            'cargo_capacity' => $starship['cargo_capacity'],
        ];
    }

    private function resourceId(string $url): int
    {
        preg_match('~/(\d+)/?$~', $url, $matches);

        return (int) ($matches[1] ?? 0);
    }

    private function cacheTtl(): int
    {
        return (int) config('services.swapi.cache_ttl', 3600);
    }
}
