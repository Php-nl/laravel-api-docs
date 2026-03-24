<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction\Extractors;

use Illuminate\Routing\Route;
use PhpNl\LaravelApiDoc\Data\Endpoint;
use PhpNl\LaravelApiDoc\Data\Parameter;
use ReflectionMethod;

final readonly class DocblockExtractor implements Extractor
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
        $docComment = $reflection->getDocComment();

        if ($docComment === false) {
            return;
        }

        $this->parseDocblock($docComment, $endpoint);
    }

    /**
     * Parse the docblock and populate the endpoint.
     *
     * @param string $docComment
     * @param Endpoint $endpoint
     */
    private function parseDocblock(string $docComment, Endpoint $endpoint): void
    {
        // Simple regex-based parsing for now
        // Extract @param tags
        preg_match_all('/@param\s+([^\s]+)\s+\$([^\s]+)(?:\s+(.*))?/', $docComment, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $type = $match[1];
            $name = $match[2];
            $description = $match[3] ?? null;

            // Check if parameter already exists (from SignatureExtractor)
            $exists = false;
            foreach ($endpoint->parameters as $parameter) {
                if ($parameter->name === $name) {
                    $exists = true;
                    // Update description if it was missing
                    if ($parameter->description === null) {
                        // This is a bit tricky since Endpoint properties are public and Parameter is readonly.
                        // For now, we'll just skip or we might need to recreate the parameter.
                    }
                    break;
                }
            }

            if (!$exists) {
                $endpoint->addParameter(new Parameter(
                    name: $name,
                    type: $type,
                    description: $description
                ));
            }
        }

        // Extract @description or the first part of the docblock
        $description = preg_replace('/^\/\*\*|\*\/|\s*\*/m', '', $docComment);
        $description = trim(preg_replace('/@[a-z]+.*/s', '', $description));

        if ($description !== '') {
            $endpoint->description = $description;
        }
    }
}
