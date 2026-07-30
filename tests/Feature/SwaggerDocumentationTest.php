<?php

namespace Tests\Feature;

use Tests\TestCase;

class SwaggerDocumentationTest extends TestCase
{
    public function test_swagger_ui_is_available(): void
    {
        $this->get('/api/documentation')
            ->assertOk()
            ->assertSee('Star Wars Explorer API');
    }

    public function test_openapi_document_contains_all_api_operations(): void
    {
        $response = $this->getJson('/docs')
            ->assertOk()
            ->assertJsonPath('info.title', 'Star Wars Explorer API')
            ->assertJsonPath('openapi', '3.0.0');

        $document = $response->json();
        $operationCount = collect($document['paths'])
            ->sum(fn (array $path): int => count(array_intersect_key(
                $path,
                array_flip(['get', 'post', 'put', 'patch', 'delete']),
            )));

        $this->assertSame(9, $operationCount);
    }
}
