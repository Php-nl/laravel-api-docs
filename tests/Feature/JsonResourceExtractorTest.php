<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Tests\Feature;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route as RouteFacade;
use PhpNl\LaravelApiDoc\Data\Endpoint;
use PhpNl\LaravelApiDoc\Extraction\Extractors\JsonResourceExtractor;
use PhpNl\LaravelApiDoc\Tests\TestCase;

final class JsonResourceExtractorTest extends TestCase
{
    public function test_it_extracts_response_from_json_resource(): void
    {
        $extractor = new JsonResourceExtractor;

        $route = RouteFacade::get('/test-resource', [ResourceController::class, 'show']);

        $endpoint = new Endpoint(uri: 'test-resource', methods: ['GET']);

        $extractor->extract($route, $endpoint);

        $this->assertCount(1, $endpoint->responses);
        $this->assertEquals(200, $endpoint->responses[0]->status);
        $this->assertStringContainsString('TestResource', $endpoint->responses[0]->description);

        $schema = $endpoint->responses[0]->schema;
        $this->assertEquals('object', $schema['type']);

        $this->assertArrayHasKey('properties', $schema);
        $this->assertEquals('number', $schema['properties']['id']['type']);
        $this->assertEquals('string', $schema['properties']['name']['type']);
    }

    public function test_it_extracts_nested_json_resources(): void
    {
        $extractor = new JsonResourceExtractor;

        $route = RouteFacade::get('/nested-resource', [NestedResourceController::class, 'show']);

        $endpoint = new Endpoint(uri: 'nested-resource', methods: ['GET']);

        $extractor->extract($route, $endpoint);

        $schema = $endpoint->responses[0]->schema;

        $this->assertEquals('object', $schema['type']);
        $this->assertArrayHasKey('user', $schema['properties']);
        $this->assertEquals('object', $schema['properties']['user']['type']);
        $this->assertEquals('string', $schema['properties']['user']['properties']['email']['type']);
    }

    public function test_it_handles_dummy_properties_safely(): void
    {
        $extractor = new JsonResourceExtractor;

        $route = RouteFacade::get('/dummy-resource', [DummyController::class, 'show']);

        $endpoint = new Endpoint(uri: 'dummy-resource', methods: ['GET']);

        $extractor->extract($route, $endpoint);

        $schema = $endpoint->responses[0]->schema;

        $this->assertEquals('object', $schema['type']);
        $this->assertArrayHasKey('id', $schema['properties']);
        $this->assertEquals('number', $schema['properties']['id']['type']);

        $this->assertArrayHasKey('is_active', $schema['properties']);
        $this->assertEquals('boolean', $schema['properties']['is_active']['type']);
    }
}

class TestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => 1,
            'name' => 'Test',
        ];
    }
}

class ResourceController
{
    public function show(): TestResource
    {
        return new TestResource(null);
    }
}

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => 2,
            'email' => 'test@example.com',
        ];
    }
}

class NestedResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'meta' => 'info',
            'user' => new UserResource(null),
        ];
    }
}

class NestedResourceController
{
    public function show(): NestedResource
    {
        return new NestedResource(null);
    }
}

class DummyTestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'is_active' => $this->is_active,
            'custom' => $this->custom_property,
        ];
    }
}

class DummyController
{
    public function show(): DummyTestResource
    {
        return new DummyTestResource(null);
    }
}
