<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Services;

final class OpenApiGenerator
{
    /**
     * @param  array<int, array<string, mixed>>  $endpoints
     * @return array<string, mixed>
     */
    public function generate(array $endpoints): array
    {
        $paths = [];

        foreach ($endpoints as $endpoint) {
            $uri = '/'.ltrim($endpoint['uri'], '/');

            // OpenAPI uses {param} syntax which Laravel also uses, but we should make sure
            // parameters are correctly grouped.
            if (! isset($paths[$uri])) {
                $paths[$uri] = [];
            }

            foreach ($endpoint['methods'] as $method) {
                $method = strtolower($method);
                if (in_array($method, ['head', 'options'], true)) {
                    continue;
                }

                $paths[$uri][$method] = $this->buildOperation($endpoint);
            }
        }

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => config('laravel-api-doc.ui.title', 'API Documentation'),
                'version' => '1.0.0',
            ],
            'servers' => [
                ['url' => url('/')],
            ],
            'paths' => (object) $paths,
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $endpoint
     * @return array<string, mixed>
     */
    private function buildOperation(array $endpoint): array
    {
        $operation = [
            'summary' => $endpoint['name'] ?? $endpoint['uri'],
            'description' => $endpoint['description'] ?? '',
            'tags' => [$endpoint['group'] ?? 'Default'],
            'parameters' => [],
            'responses' => [],
        ];

        if ($endpoint['auth_required'] ?? false) {
            $operation['security'] = [['bearerAuth' => []]];
        }

        $bodyParams = [];

        foreach ($endpoint['parameters'] as $parameter) {
            if ($parameter['in'] === 'body') {
                $bodyParams[] = $parameter;

                continue;
            }

            $operation['parameters'][] = [
                'name' => $parameter['name'],
                'in' => $parameter['in'], // path or query
                'required' => $parameter['required'],
                'description' => $parameter['description'] ?? implode(', ', $parameter['rules'] ?? []),
                'schema' => [
                    'type' => $this->mapType($parameter['type']),
                    'default' => $parameter['default'],
                ],
            ];
        }

        if (! empty($bodyParams)) {
            $properties = [];
            $required = [];

            foreach ($bodyParams as $param) {
                $properties[$param['name']] = [
                    'type' => $this->mapType($param['type']),
                    'description' => $param['description'] ?? implode(', ', $param['rules'] ?? []),
                    'default' => $param['default'],
                ];
                if ($param['required']) {
                    $required[] = $param['name'];
                }
            }

            $operation['requestBody'] = [
                'required' => ! empty($required),
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => (object) $properties,
                            'required' => ! empty($required) ? $required : null,
                        ],
                    ],
                ],
            ];

            // Remove null required if empty
            if (empty($required)) {
                unset($operation['requestBody']['content']['application/json']['schema']['required']);
            }
        }

        if (empty($endpoint['responses'])) {
            $operation['responses']['200'] = [
                'description' => 'Successful response',
            ];
        } else {
            foreach ($endpoint['responses'] as $response) {
                $opResponse = [
                    'description' => $response['description'] ?? 'Response',
                ];

                if (! empty($response['schema'])) {
                    $opResponse['content'] = [
                        'application/json' => [
                            'schema' => $this->formatSchemaForOpenApi($response['schema']),
                        ],
                    ];
                }

                $operation['responses'][(string) $response['status']] = $opResponse;
            }
        }

        return $operation;
    }

    private function mapType(string $type): string
    {
        return match (strtolower($type)) {
            'int', 'integer' => 'integer',
            'float', 'double' => 'number',
            'bool', 'boolean' => 'boolean',
            'array' => 'array',
            'object' => 'object',
            default => 'string',
        };
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    private function formatSchemaForOpenApi(array $schema): array
    {
        $formatted = ['type' => $schema['type'] ?? 'object'];

        if (isset($schema['example'])) {
            $formatted['example'] = $schema['example'];
        }

        if (isset($schema['properties']) && is_array($schema['properties'])) {
            $props = [];
            foreach ($schema['properties'] as $key => $prop) {
                $props[$key] = $this->formatSchemaForOpenApi($prop);
            }
            $formatted['properties'] = (object) $props;
        }

        if (isset($schema['items']) && is_array($schema['items'])) {
            $formatted['items'] = $this->formatSchemaForOpenApi($schema['items']);
        }

        return $formatted;
    }
}
