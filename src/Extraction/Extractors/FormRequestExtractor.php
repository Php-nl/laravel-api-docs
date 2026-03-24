<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction\Extractors;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use PhpNl\LaravelApiDoc\Data\Endpoint;
use PhpNl\LaravelApiDoc\Data\Parameter;
use ReflectionMethod;
use ReflectionNamedType;

final readonly class FormRequestExtractor implements Extractor
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

        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType) {
                continue;
            }

            $className = $type->getName();

            if (is_subclass_of($className, FormRequest::class)) {
                $this->extractFromFormRequest($className, $endpoint);
            }
        }
    }

    /**
     * Extract parameters from a FormRequest class.
     *
     * @param string $className
     * @param Endpoint $endpoint
     */
    private function extractFromFormRequest(string $className, Endpoint $endpoint): void
    {
        /** @var FormRequest $formRequest */
        $formRequest = new $className();

        if (!method_exists($formRequest, 'rules')) {
            return;
        }

        $rules = $formRequest->rules();

        foreach ($rules as $name => $rule) {
            $ruleString = is_array($rule) ? implode('|', $rule) : (string) $rule;

            // Simple parsing of rules to determine type and requirement
            $isRequired = str_contains($ruleString, 'required');
            $type = $this->determineType($ruleString);

            // Check if parameter already exists
            $exists = false;
            foreach ($endpoint->parameters as $parameter) {
                if ($parameter->name === $name) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $endpoint->addParameter(new Parameter(
                    name: $name,
                    type: $type,
                    required: $isRequired,
                    description: "Validation rules: {$ruleString}"
                ));
            }
        }
    }

    /**
     * Determine the parameter type based on validation rules.
     *
     * @param string $rules
     * @return string
     */
    private function determineType(string $rules): string
    {
        if (str_contains($rules, 'integer') || str_contains($rules, 'numeric')) {
            return 'integer';
        }

        if (str_contains($rules, 'boolean')) {
            return 'boolean';
        }

        if (str_contains($rules, 'array')) {
            return 'array';
        }

        if (str_contains($rules, 'date')) {
            return 'date';
        }

        return 'string';
    }
}
