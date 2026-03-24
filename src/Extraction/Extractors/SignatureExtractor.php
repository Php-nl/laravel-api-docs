<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction\Extractors;

use Illuminate\Routing\Route;
use PhpNl\LaravelApiDoc\Data\Endpoint;
use PhpNl\LaravelApiDoc\Data\Parameter;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

final readonly class SignatureExtractor implements Extractor
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

        [$controller, $method] = explode('@', $action['controller']);

        if (!method_exists($controller, $method)) {
            return;
        }

        $reflection = new ReflectionMethod($controller, $method);

        foreach ($reflection->getParameters() as $parameter) {
            if ($this->shouldSkip($parameter)) {
                continue;
            }

            $endpoint->addParameter($this->createParameter($parameter));
        }
    }

    /**
     * Determine if a parameter should be skipped.
     *
     * @param ReflectionParameter $parameter
     * @return bool
     */
    private function shouldSkip(ReflectionParameter $parameter): bool
    {
        $type = $parameter->getType();

        if (!$type instanceof ReflectionNamedType) {
            return false;
        }

        // Skip Laravel's internal classes and Request objects
        $name = $type->getName();

        return str_starts_with($name, 'Illuminate\\') || str_contains($name, 'Request');
    }

    /**
     * Create a Parameter DTO from a reflection parameter.
     *
     * @param ReflectionParameter $parameter
     * @return Parameter
     */
    private function createParameter(ReflectionParameter $parameter): Parameter
    {
        $type = 'string';
        $reflectionType = $parameter->getType();

        if ($reflectionType instanceof ReflectionNamedType) {
            $type = $reflectionType->getName();
        }

        return new Parameter(
            name: $parameter->getName(),
            type: $type,
            required: !$parameter->isOptional(),
            default: $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
        );
    }
}
