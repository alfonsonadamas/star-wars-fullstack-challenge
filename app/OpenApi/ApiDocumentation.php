<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Star Wars Explorer API',
    description: 'API para consultar información normalizada de SWAPI y administrar naves guardadas.',
)]
#[OA\Server(url: '/api', description: 'API de la aplicación')]
#[OA\Tag(name: 'SWAPI', description: 'Consultas de películas y naves obtenidas desde SWAPI')]
#[OA\Tag(name: 'Naves guardadas', description: 'CRUD de naves almacenadas en la base de datos local')]
class ApiDocumentation {}

#[OA\Schema(
    schema: 'Film',
    required: ['id', 'title', 'episode_id', 'director', 'release_date', 'starships_count'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'A New Hope'),
        new OA\Property(property: 'episode_id', type: 'integer', example: 4),
        new OA\Property(property: 'director', type: 'string', example: 'George Lucas'),
        new OA\Property(property: 'release_date', type: 'string', format: 'date', example: '1977-05-25'),
        new OA\Property(property: 'starships_count', type: 'integer', example: 8),
    ],
    type: 'object',
)]
class FilmSchema {}

#[OA\Schema(
    schema: 'ExternalStarship',
    required: ['id', 'name', 'model', 'manufacturer', 'crew', 'max_atmosphering_speed', 'cargo_capacity'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 10),
        new OA\Property(property: 'name', type: 'string', example: 'Millennium Falcon'),
        new OA\Property(property: 'model', type: 'string', example: 'YT-1300 light freighter'),
        new OA\Property(property: 'manufacturer', type: 'string', example: 'Corellian Engineering Corporation'),
        new OA\Property(property: 'crew', type: 'string', example: '4'),
        new OA\Property(property: 'max_atmosphering_speed', type: 'string', example: '1050'),
        new OA\Property(property: 'cargo_capacity', type: 'string', example: '100000'),
    ],
    type: 'object',
)]
class ExternalStarshipSchema {}

#[OA\Schema(
    schema: 'SavedStarship',
    required: ['id', 'swapi_id', 'name', 'max_atmosphering_speed', 'cargo_capacity', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'swapi_id', type: 'integer', nullable: true, example: 10),
        new OA\Property(property: 'name', type: 'string', maxLength: 80, example: 'Millennium Falcon'),
        new OA\Property(property: 'max_atmosphering_speed', type: 'integer', maximum: 999999, minimum: 0, example: 1050),
        new OA\Property(property: 'cargo_capacity', type: 'integer', format: 'int64', maximum: 999999999999999, minimum: 0, example: 100000),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-07-30T18:00:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-07-30T18:00:00.000000Z'),
    ],
    type: 'object',
)]
class SavedStarshipSchema {}

#[OA\Schema(
    schema: 'SavedStarshipInput',
    required: ['name', 'max_atmosphering_speed', 'cargo_capacity'],
    properties: [
        new OA\Property(property: 'swapi_id', type: 'integer', nullable: true, minimum: 1, example: 10),
        new OA\Property(property: 'name', type: 'string', maxLength: 80, example: 'Millennium Falcon'),
        new OA\Property(property: 'max_atmosphering_speed', type: 'integer', maximum: 999999, minimum: 0, example: 1050),
        new OA\Property(property: 'cargo_capacity', type: 'integer', format: 'int64', maximum: 999999999999999, minimum: 0, example: 100000),
    ],
    type: 'object',
)]
class SavedStarshipInputSchema {}

#[OA\Schema(
    schema: 'ApiMessage',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'No fue posible consultar la información de Star Wars.'),
    ],
    type: 'object',
)]
class ApiMessageSchema {}

#[OA\Schema(
    schema: 'ValidationError',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The name field is required.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string'),
            ),
            example: ['name' => ['The name field is required.']],
        ),
    ],
    type: 'object',
)]
class ValidationErrorSchema {}

#[OA\Parameter(
    parameter: 'SavedStarshipId',
    name: 'id',
    description: 'ID local de la nave guardada',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer', minimum: 1, example: 1),
)]
class SavedStarshipIdParameter {}

class SwapiEndpoints
{
    #[OA\Get(
        path: '/swapi/films',
        operationId: 'listFilms',
        summary: 'Listar películas',
        tags: ['SWAPI'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Películas normalizadas',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Film'),
                        ),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 502,
                description: 'SWAPI no está disponible',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiMessage'),
            ),
        ],
    )]
    public function films(): void {}

    #[OA\Get(
        path: '/swapi/films/{film}/starships',
        operationId: 'listFilmStarships',
        summary: 'Listar las naves de una película',
        tags: ['SWAPI'],
        parameters: [
            new OA\Parameter(
                name: 'film',
                description: 'ID de la película en SWAPI',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', minimum: 1, example: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Película y naves asociadas',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            required: ['film', 'starships'],
                            properties: [
                                new OA\Property(property: 'film', ref: '#/components/schemas/Film'),
                                new OA\Property(
                                    property: 'starships',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/ExternalStarship'),
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 502,
                description: 'SWAPI no está disponible',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiMessage'),
            ),
        ],
    )]
    public function filmStarships(): void {}

    #[OA\Get(
        path: '/swapi/starships/{starship}',
        operationId: 'showExternalStarship',
        summary: 'Consultar una nave de SWAPI',
        tags: ['SWAPI'],
        parameters: [
            new OA\Parameter(
                name: 'starship',
                description: 'ID de la nave en SWAPI',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', minimum: 1, example: 10),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Datos principales de la nave',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/ExternalStarship'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 502,
                description: 'SWAPI no está disponible',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiMessage'),
            ),
        ],
    )]
    public function starship(): void {}
}

class SavedStarshipEndpoints
{
    #[OA\Get(
        path: '/starships',
        operationId: 'listSavedStarships',
        summary: 'Listar naves guardadas',
        tags: ['Naves guardadas'],
        parameters: [
            new OA\Parameter(
                name: 'swapi_id',
                description: 'Filtrar por ID externo de SWAPI',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, example: 10),
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Página del listado',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, example: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado paginado',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/SavedStarship'),
                        ),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/starships',
        operationId: 'createSavedStarship',
        summary: 'Guardar una nave',
        tags: ['Naves guardadas'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/SavedStarshipInput'),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Nave guardada',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/SavedStarship'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 422,
                description: 'Datos inválidos o nave duplicada',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
            ),
        ],
    )]
    public function store(): void {}

    #[OA\Get(
        path: '/starships/{id}',
        operationId: 'showSavedStarship',
        summary: 'Consultar una nave guardada',
        tags: ['Naves guardadas'],
        parameters: [new OA\Parameter(ref: '#/components/parameters/SavedStarshipId')],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Nave guardada',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/SavedStarship'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(response: 404, description: 'Nave no encontrada'),
        ],
    )]
    public function show(): void {}

    #[OA\Patch(
        path: '/starships/{id}',
        operationId: 'updateSavedStarship',
        summary: 'Actualizar una nave guardada',
        tags: ['Naves guardadas'],
        parameters: [new OA\Parameter(ref: '#/components/parameters/SavedStarshipId')],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/SavedStarshipInput'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Nave actualizada',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/SavedStarship'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(response: 404, description: 'Nave no encontrada'),
            new OA\Response(
                response: 422,
                description: 'Datos inválidos',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
            ),
        ],
    )]
    public function update(): void {}

    #[OA\Put(
        path: '/starships/{id}',
        operationId: 'replaceSavedStarship',
        summary: 'Reemplazar una nave guardada',
        tags: ['Naves guardadas'],
        parameters: [new OA\Parameter(ref: '#/components/parameters/SavedStarshipId')],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/SavedStarshipInput'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Nave actualizada',
                content: new OA\JsonContent(
                    required: ['data'],
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/SavedStarship'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(response: 404, description: 'Nave no encontrada'),
            new OA\Response(
                response: 422,
                description: 'Datos inválidos',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
            ),
        ],
    )]
    public function replace(): void {}

    #[OA\Delete(
        path: '/starships/{id}',
        operationId: 'deleteSavedStarship',
        summary: 'Eliminar una nave guardada',
        tags: ['Naves guardadas'],
        parameters: [new OA\Parameter(ref: '#/components/parameters/SavedStarshipId')],
        responses: [
            new OA\Response(response: 204, description: 'Nave eliminada'),
            new OA\Response(response: 404, description: 'Nave no encontrada'),
        ],
    )]
    public function destroy(): void {}
}
