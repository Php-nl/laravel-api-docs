<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction\Extractors;

use Illuminate\Routing\Route;
use PhpNl\LaravelApiDoc\Attributes\ApiDoc;
use PhpNl\LaravelApiDoc\Data\Endpoint;
use PhpNl\LaravelApiDoc\Data\Parameter;
use ReflectionMethod;

final readonly class AttributeExtractor implements Extractor
{
    /**
     * Extract information from a route and populate the endpoint.
     */
    public function extract(Route $route, Endpoint $endpoint): void
    {
        $action = $route->getAction();

        if (! isset($action['controller']) || ! is_string($action['controller'])) {
            return;
        }

        if (str_contains($action['controller'], '@')) {
            [$controller, $method] = explode('@', $action['controller']);
        } else {
            $controller = $action['controller'];
            $method = '__invoke';
        }

        if (! method_exists($controller, $method)) {
            return;
        }

        $reflection = new ReflectionMethod($controller, $method);
        $attributes = $reflection->getAttributes(ApiDoc::class);

        if (! empty($attributes)) {
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

        // Handle #[Unauthenticated]
        $reflectionClass = new \ReflectionClass($controller);
        if (
            ! empty($reflectionClass->getAttributes(\PhpNl\LaravelApiDoc\Attributes\Unauthenticated::class)) ||
            ! empty($reflection->getAttributes(\PhpNl\LaravelApiDoc\Attributes\Unauthenticated::class))
        ) {
            $endpoint->authRequired = false;
        }

        // Handle #[QueryParam]
        $queryParams = $reflection->getAttributes(\PhpNl\LaravelApiDoc\Attributes\QueryParam::class);
        foreach ($queryParams as $paramAttr) {
            /** @var \PhpNl\LaravelApiDoc\Attributes\QueryParam $param */
            $param = $paramAttr->newInstance();

            $endpoint->addParameter(new Parameter(
                name: $param->name,
                type: $param->type,
                required: $param->required,
                description: $param->description,
                in: 'query',
                rules: [],
                enumValues: ! empty($param->enumValues) ? $param->enumValues : null,
                example: $param->example
            ));
        }

        // Handle #[BodyParam]
        $bodyParams = $reflection->getAttributes(\PhpNl\LaravelApiDoc\Attributes\BodyParam::class);
        foreach ($bodyParams as $paramAttr) {
            /** @var \PhpNl\LaravelApiDoc\Attributes\BodyParam $param */
            $param = $paramAttr->newInstance();

            $endpoint->addParameter(new Parameter(
                name: $param->name,
                type: $param->type,
                required: $param->required,
                description: $param->description,
                in: 'body',
                rules: [],
                enumValues: ! empty($param->enumValues) ? $param->enumValues : null,
                example: $param->example
            ));
        }
    }
}
