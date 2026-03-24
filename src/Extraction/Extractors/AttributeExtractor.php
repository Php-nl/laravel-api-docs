<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction\Extractors;

use Illuminate\Routing\Route;
use PhpNl\LaravelApiDoc\Attributes\ApiDoc;
use PhpNl\LaravelApiDoc\Data\Endpoint;
use ReflectionMethod;

final readonly class AttributeExtractor implements Extractor
{
    /**
     * Extract information from a route and populate the endpoint.
     */
    public function extract(Route $route, Endpoint $endpoint): void
    {
        $action = $route->getAction();

        if (!isset($action['controller']) || !is_string($action['controller'])) {
            return;
        }

        if (str_contains($action['controller'], '@')) {
            [$controller, $method] = explode('@', $action['controller']);
        } else {
            $controller = $action['controller'];
            $method = '__invoke';
        }

        if (!method_exists($controller, $method)) {
            return;
        }

        $reflection = new ReflectionMethod($controller, $method);
        $attributes = $reflection->getAttributes(ApiDoc::class);

        if (empty($attributes)) {
            return;
        }

        $attribute = $attributes[0]->newInstance();

        if ($attribute->name !== null) {
            $endpoint->name = $attribute->name;
        }

        if ($attribute->group !== null) {
            $endpoint->group = $attribute->group;
        }

        if ($attribute->description !== null) {
            $endpoint->description = $attribute->description;
        }
    }
}
