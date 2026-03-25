<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction\Extractors;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Route;
use PhpNl\LaravelApiDoc\Data\Endpoint;
use PhpNl\LaravelApiDoc\Data\Response;
use PhpNl\LaravelApiDoc\Extraction\Support\DummyResourceModel;
use ReflectionMethod;
use ReflectionNamedType;
use Throwable;

final readonly class JsonResourceExtractor implements Extractor
{
    /**
     * @param Route $route
     * @param Endpoint $endpoint
     * @return void
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
        $returnType = $reflection->getReturnType();

        if (!$returnType instanceof ReflectionNamedType) {
            return;
        }

        $className = $returnType->getName();

        if (is_subclass_of($className, JsonResource::class)) {
            $endpoint->addResponse(new Response(
                status: 200,
                description: "Successful response returning {$className}",
                schema: $this->extractSchema($className)
            ));
        }
    }

    /**
     * @param string $className
     * @return array<string, mixed>
     */
    private function extractSchema(string $className): array
    {
        $modelClass = $this->inferModelClass($className);
        
        if ($modelClass) {
            $reader = new \PhpNl\LaravelApiDoc\Extraction\Support\EloquentSchemaReader();
            $properties = $reader->read($modelClass);
            
            if (!empty($properties)) {
                return [
                    'type' => 'object',
                    'resource' => $className,
                    'properties' => $properties,
                ];
            }
        }

        try {
            /** @var JsonResource $resource */
            $resource = new $className(new DummyResourceModel());
            $payload = $resource->toArray(request());

            return [
                'type' => 'object',
                'resource' => $className,
                'properties' => $this->mapArrayToSchema($payload),
            ];
        } catch (Throwable) {
            return [
                'type' => 'object',
                'resource' => $className,
            ];
        }
    }

    private function inferModelClass(string $resourceClass): ?string
    {
        if (!class_exists($resourceClass)) return null;

        $reflection = new \ReflectionClass($resourceClass);
        $docBlock = $reflection->getDocComment() ?: '';

        if (preg_match('/@mixin\s+([A-Za-z0-9_\\\\]+)/', $docBlock, $matches)) {
            $class = ltrim($matches[1], '\\');
            if (class_exists($class)) {
                return $class;
            }
        }

        if (preg_match('/@property\s+([A-Za-z0-9_\\\\]+)\s+\$resource/', $docBlock, $matches)) {
            $class = ltrim($matches[1], '\\');
            if (class_exists($class)) {
                return $class;
            }
        }

        $baseName = class_basename($resourceClass);
        $modelName = str_replace('Resource', '', $baseName);

        $possibleNamespaces = [
            "\\App\\Models\\",
            "\\App\\",
        ];

        foreach ($possibleNamespaces as $namespace) {
            $class = $namespace . $modelName;
            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }

    /**
     * @param array<int|string, mixed> $payload
     * @return array<string, mixed>
     */
    private function mapArrayToSchema(array $payload): array
    {
        $schema = [];

        foreach ($payload as $key => $value) {
            $type = gettype($value);

            if ($value instanceof JsonResource) {
                $schema[$key] = [
                    'type' => 'object',
                    'properties' => $this->mapArrayToSchema($value->toArray(request())),
                ];
                continue;
            }

            if ($type === 'array') {
                if (array_is_list($value)) {
                    $itemType = count($value) > 0 ? gettype($value[0]) : 'mixed';

                    if ($itemType === 'object' && $value[0] instanceof JsonResource) {
                        $schema[$key] = [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => $this->mapArrayToSchema($value[0]->toArray(request())),
                            ],
                        ];
                        continue;
                    }

                    $schema[$key] = [
                        'type' => 'array',
                        'items' => [
                            'type' => $itemType === 'array' ? 'object' : $itemType,
                            'properties' => $itemType === 'array' ? $this->mapArrayToSchema($value[0] ?? []) : null,
                        ],
                    ];
                } else {
                    $schema[$key] = [
                        'type' => 'object',
                        'properties' => $this->mapArrayToSchema($value),
                    ];
                }
                continue;
            }

            if (is_object($value)) {
                if (method_exists($value, '__toString')) {
                    $value = (string) $value;
                } elseif ($value instanceof \JsonSerializable) {
                    $value = json_encode($value);
                } else {
                    $value = get_class($value);
                }
                $type = 'string';
            }

            $schema[$key] = [
                'type' => match ($type) {
                    'integer', 'double' => 'number',
                    'boolean' => 'boolean',
                    default => 'string',
                },
                'example' => $value,
            ];
        }

        return $schema;
    }
}
