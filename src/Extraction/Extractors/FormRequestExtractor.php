<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction\Extractors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\In;
use PhpNl\LaravelApiDoc\Data\Endpoint;
use PhpNl\LaravelApiDoc\Data\Parameter;
use PhpNl\LaravelApiDoc\Extraction\SchemaRegistry;
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

        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (! $type instanceof ReflectionNamedType) {
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
     */
    private function extractFromFormRequest(string $className, Endpoint $endpoint): void
    {
        /** @var FormRequest $formRequest */
        $formRequest = new $className;

        if (! method_exists($formRequest, 'rules')) {
            return;
        }

        $rules = $formRequest->rules();

        $in = 'query';
        if (array_intersect(['POST', 'PUT', 'PATCH'], $endpoint->methods)) {
            $in = 'body';
        }

        $addedParameters = [];

        foreach ($rules as $name => $rule) {
            $ruleArray = [];
            $enumValues = null;

            if (is_array($rule)) {
                foreach ($rule as $r) {
                    if ($r instanceof Enum) {
                        try {
                            $refProperty = new \ReflectionProperty($r, 'type');
                            $refProperty->setAccessible(true);
                            $enumClass = $refProperty->getValue($r);

                            if (function_exists('enum_exists') && enum_exists($enumClass)) {
                                $enumValues = array_map(fn ($case) => $case->value ?? $case->name, $enumClass::cases());
                                $ruleArray[] = 'enum:'.implode(',', $enumValues);

                                SchemaRegistry::register(class_basename($enumClass), [
                                    'type' => 'string',
                                    'enum' => $enumValues,
                                ]);

                                continue;
                            }
                        } catch (\Throwable) {
                        }
                    }

                    if (is_string($r) && function_exists('enum_exists') && enum_exists($r)) {
                        $enumValues = array_map(fn ($case) => $case->value ?? $case->name, $r::cases());
                        $ruleArray[] = 'enum:'.implode(',', $enumValues);

                        SchemaRegistry::register(class_basename($r), [
                            'type' => 'string',
                            'enum' => $enumValues,
                        ]);

                        continue;
                    }

                    if ($r instanceof In) {
                        try {
                            $refProperty = new \ReflectionProperty($r, 'values');
                            $refProperty->setAccessible(true);
                            $enumValues = $refProperty->getValue($r);
                            $ruleArray[] = 'in:'.implode(',', $enumValues);

                            continue;
                        } catch (\Throwable) {
                        }
                    }

                    $ruleArray[] = is_object($r) ? get_class($r) : (string) $r;
                }
            } else {
                $ruleArray = explode('|', (string) $rule);
                foreach ($ruleArray as $idx => $r) {
                    if (str_starts_with($r, 'enum:')) {
                        $enumClass = substr($r, 5);
                        if (function_exists('enum_exists') && enum_exists($enumClass)) {
                            $enumValues = array_map(fn ($case) => $case->value ?? $case->name, $enumClass::cases());
                            $ruleArray[$idx] = 'enum:'.implode(',', $enumValues);

                            SchemaRegistry::register(class_basename($enumClass), [
                                'type' => 'string',
                                'enum' => $enumValues,
                            ]);
                        }
                    } elseif (str_starts_with($r, 'in:')) {
                        $enumValues = explode(',', substr($r, 3));
                    }
                }
            }

            $ruleString = implode('|', $ruleArray);

            $isRequired = in_array('required', $ruleArray) || str_contains($ruleString, 'required');
            $type = $this->determineType($ruleString);

            $exists = false;
            foreach ($endpoint->parameters as $parameter) {
                if ($parameter->name === $name) {
                    $exists = true;
                    break;
                }
            }

            if (! $exists) {
                $newParameter = new Parameter(
                    name: $name,
                    type: $type,
                    required: $isRequired,
                    description: null,
                    in: $in,
                    rules: $ruleArray,
                    enumValues: $enumValues
                );
                $endpoint->addParameter($newParameter);
                $addedParameters[] = $newParameter;
            } else {
                // If it already exists, we should still include it in the schema if it's part of rules
                foreach ($endpoint->parameters as $p) {
                    if ($p->name === $name) {
                        $addedParameters[] = $p;
                        break;
                    }
                }
            }
        }

        $this->introspectModelFromRequest($className, $endpoint, $in, $addedParameters);

        // Also register the full form request as a Schema
        $this->registerSchema($className, $addedParameters);
    }

    /**
     * Determine the parameter type based on validation rules.
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

        if (str_contains($rules, 'file') || str_contains($rules, 'image')) {
            return 'file';
        }

        return 'string';
    }

    /**
     * Introspect the database schema and model casts to find undocumented fillable properties.
     *
     * @param  array<Parameter>  &$addedParameters
     */
    private function introspectModelFromRequest(string $requestClass, Endpoint $endpoint, string $in, array &$addedParameters): void
    {
        $basename = class_basename($requestClass);
        $modelName = str_replace(['Store', 'Update', 'Request'], '', $basename);
        $modelClass = 'App\\Models\\'.$modelName;

        if (! class_exists($modelClass)) {
            return;
        }

        try {
            /** @var Model $model */
            $model = new $modelClass;
            $table = $model->getTable();
            $fillable = $model->getFillable();

            if (empty($fillable)) {
                return;
            }

            $columns = Schema::getColumns($table);
            $dbColumns = [];
            foreach ($columns as $col) {
                $dbColumns[$col['name']] = $col;
            }

            foreach ($fillable as $column) {
                $exists = false;
                foreach ($endpoint->parameters as $parameter) {
                    if ($parameter->name === $column) {
                        $exists = true;
                        break;
                    }
                }

                if ($exists) {
                    continue;
                }

                $type = 'string';
                $required = false;

                if (isset($dbColumns[$column])) {
                    $dbTypeStr = strtolower($dbColumns[$column]['type_name'] ?? '');
                    if (str_contains($dbTypeStr, 'int')) {
                        $type = 'integer';
                    } elseif (str_contains($dbTypeStr, 'bool') || str_contains($dbTypeStr, 'tinyint(1)')) {
                        $type = 'boolean';
                    } elseif (str_contains($dbTypeStr, 'json')) {
                        $type = 'array';
                    } elseif (str_contains($dbTypeStr, 'date')) {
                        $type = 'date';
                    } elseif (str_contains($dbTypeStr, 'float') || str_contains($dbTypeStr, 'double') || str_contains($dbTypeStr, 'decimal')) {
                        $type = 'number';
                    }

                    $required = ! ($dbColumns[$column]['nullable'] ?? true);
                }

                $casts = $model->getCasts();
                if (isset($casts[$column])) {
                    $cast = strtolower((string) $casts[$column]);
                    if (str_contains($cast, 'int')) {
                        $type = 'integer';
                    } elseif (str_contains($cast, 'bool')) {
                        $type = 'boolean';
                    } elseif (str_contains($cast, 'array') || str_contains($cast, 'json')) {
                        $type = 'array';
                    } elseif (str_contains($cast, 'date') || str_contains($cast, 'datetime')) {
                        $type = 'date';
                    }
                }

                $newParameter = new Parameter(
                    name: $column,
                    type: $type,
                    required: $required,
                    description: 'Auto-extracted from '.$modelName.' database schema.',
                    in: $in,
                    rules: $required ? ['required'] : [],
                    enumValues: null
                );

                $endpoint->addParameter($newParameter);
                $addedParameters[] = $newParameter;
            }
        } catch (\Throwable) {
            // Ignore if DB is not reachable or table doesn't exist
        }
    }

    /**
     * @param  array<Parameter>  $parameters
     */
    private function registerSchema(string $requestClass, array $parameters): void
    {
        $properties = [];
        $required = [];

        foreach ($parameters as $parameter) {
            $prop = [
                'type' => $parameter->type,
                'description' => $parameter->description,
            ];

            if ($parameter->enumValues) {
                $prop['enum'] = $parameter->enumValues;
            }

            if (! empty($parameter->rules)) {
                $prop['rules'] = $parameter->rules;

                // Add OpenAPI standard constraints
                foreach ($parameter->rules as $rule) {
                    $rule = (string) $rule;
                    if (str_starts_with($rule, 'min:')) {
                        $val = (float) substr($rule, 4);
                        if ($parameter->type === 'string') {
                            $prop['minLength'] = (int) $val;
                        } elseif ($parameter->type === 'number' || $parameter->type === 'integer') {
                            $prop['minimum'] = $val;
                        } elseif ($parameter->type === 'array') {
                            $prop['minItems'] = (int) $val;
                        }
                    } elseif (str_starts_with($rule, 'max:')) {
                        $val = (float) substr($rule, 4);
                        if ($parameter->type === 'string') {
                            $prop['maxLength'] = (int) $val;
                        } elseif ($parameter->type === 'number' || $parameter->type === 'integer') {
                            $prop['maximum'] = $val;
                        } elseif ($parameter->type === 'array') {
                            $prop['maxItems'] = (int) $val;
                        }
                    } elseif (str_starts_with($rule, 'size:')) {
                        $val = (float) substr($rule, 5);
                        if ($parameter->type === 'string') {
                            $prop['minLength'] = (int) $val;
                            $prop['maxLength'] = (int) $val;
                        } elseif ($parameter->type === 'array') {
                            $prop['minItems'] = (int) $val;
                            $prop['maxItems'] = (int) $val;
                        }
                    } elseif ($rule === 'email') {
                        $prop['format'] = 'email';
                    } elseif ($rule === 'uuid') {
                        $prop['format'] = 'uuid';
                    } elseif (in_array($rule, ['url', 'active_url'])) {
                        $prop['format'] = 'uri';
                    } elseif ($rule === 'ipv4') {
                        $prop['format'] = 'ipv4';
                    } elseif ($rule === 'ipv6') {
                        $prop['format'] = 'ipv6';
                    } elseif ($parameter->type === 'date') {
                        $prop['format'] = 'date-time'; // Default
                        if ($rule === 'date_format:Y-m-d') {
                            $prop['format'] = 'date';
                        }
                    }
                }
            }

            // Parse dot notation
            $this->applyDotNotation($properties, explode('.', $parameter->name), $prop);

            if ($parameter->required) {
                $required[] = $parameter->name;
            }
        }

        // Clean up required array to only include root level required fields
        // or we could build a nested required structure if needed, but for simplicity
        // standard OpenAPI allows required at each object level.
        $rootRequired = [];
        foreach ($required as $req) {
            $parts = explode('.', $req);
            if (count($parts) === 1) {
                $rootRequired[] = $req;
            }
        }

        $schema = [
            'type' => 'object',
            'properties' => $properties,
        ];

        if (! empty($rootRequired)) {
            $schema['required'] = array_unique($rootRequired);
        }

        SchemaRegistry::register(class_basename($requestClass), $schema);
    }

    /**
     * @param  array<string, mixed>  &$schema
     * @param  array<int, string>  $parts
     * @param  array<string, mixed>  $prop
     */
    private function applyDotNotation(array &$schema, array $parts, array $prop): void
    {
        $current = array_shift($parts);

        if (count($parts) === 0) {
            // Leaf node
            if ($current === '*') {
                // Should not happen at root, but if it does...
                $schema = $prop;
            } else {
                // If it already exists (e.g., an array definition without items yet), merge it
                if (isset($schema[$current]) && is_array($schema[$current])) {
                    $schema[$current] = array_merge($schema[$current], $prop);
                } else {
                    $schema[$current] = $prop;
                }
            }

            return;
        }

        if ($current === '*') {
            // We are inside an array type. The schema here is the `items` object.
            // But wait, the parent called `applyDotNotation($schema, ...)` where `$schema` is the `items` array?
            // No, the parent called `applyDotNotation($schema['items'], ...)`.
            // So if `$current` is '*', we just apply to the current schema directly as the items.
            $this->applyDotNotation($schema, $parts, $prop);

            return;
        }

        if (! isset($schema[$current])) {
            $next = $parts[0];
            if ($next === '*') {
                $schema[$current] = [
                    'type' => 'array',
                    'items' => [],
                ];
            } else {
                $schema[$current] = [
                    'type' => 'object',
                    'properties' => [],
                ];
            }
        }

        if (isset($schema[$current]['type']) && $schema[$current]['type'] === 'array') {
            if ($parts[0] === '*') {
                // Next is '*', skip it and go to its children inside 'items'
                array_shift($parts);
                if (count($parts) === 0) {
                    $schema[$current]['items'] = $prop;
                } else {
                    if (! isset($schema[$current]['items']) || ! is_array($schema[$current]['items'])) {
                        $schema[$current]['items'] = [];
                    }
                    if (! isset($schema[$current]['items']['properties']) || ! is_array($schema[$current]['items']['properties'])) {
                        $schema[$current]['items']['properties'] = [];
                        $schema[$current]['items']['type'] = 'object';
                    }
                    $this->applyDotNotation($schema[$current]['items']['properties'], $parts, $prop);
                }
            } else {
                // Malformed rule, treating as object
                if (! isset($schema[$current]['properties']) || ! is_array($schema[$current]['properties'])) {
                    $schema[$current]['properties'] = [];
                }
                $this->applyDotNotation($schema[$current]['properties'], $parts, $prop);
            }
        } else {
            // Object type
            if (! isset($schema[$current]['properties']) || ! is_array($schema[$current]['properties'])) {
                $schema[$current]['properties'] = [];
            }
            $this->applyDotNotation($schema[$current]['properties'], $parts, $prop);
        }
    }
}
