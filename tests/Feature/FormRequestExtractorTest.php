<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Tests\Feature;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use PhpNl\LaravelApiDoc\Data\Endpoint;
use PhpNl\LaravelApiDoc\Extraction\Extractors\FormRequestExtractor;
use PhpNl\LaravelApiDoc\Tests\TestCase;

final class FormRequestExtractorTest extends TestCase
{
    public function test_it_extracts_parameters_from_form_request()
    {
        $extractor = new FormRequestExtractor;

        $route = RouteFacade::post('/test', [TestController::class, 'store']);
        // Laravel 11/12 specific way to get the route object if needed,
        // but RouteFacade::post returns the Route object.

        $endpoint = new Endpoint(uri: 'test', methods: ['POST']);

        $extractor->extract($route, $endpoint);

        $this->assertCount(2, $endpoint->parameters);

        $this->assertEquals('name', $endpoint->parameters[0]->name);
        $this->assertEquals('string', $endpoint->parameters[0]->type);
        $this->assertTrue($endpoint->parameters[0]->required);

        $this->assertEquals('age', $endpoint->parameters[1]->name);
        $this->assertEquals('integer', $endpoint->parameters[1]->type);
        $this->assertFalse($endpoint->parameters[1]->required);
    }
}

class TestFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'age' => ['nullable', 'integer', 'min:18'],
        ];
    }
}

class TestController
{
    public function store(TestFormRequest $request)
    {
        return response()->json(['status' => 'ok']);
    }
}
