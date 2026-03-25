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

        $in = 'query';
        if (array_intersect(['POST', 'PUT', 'PATCH'], $endpoint->methods)) {
            $in = 'body';
        }

        foreach ($rules as $name => $rule) {
            $ruleArray = [];
            $enumValues = null;

            if (is_array($rule)) {
                foreach ($rule as $r) {
                    if ($r instanceof \Illuminate\Validation\Rules\Enum) {
                        try {
                            $refProperty = new \ReflectionProperty($r, 'type');
                            $refProperty->setAccessible(true);
                            $enumClass = $refProperty->getValue($r);
                            
                            if (function_exists('enum_exists') && enum_exists($enumClass)) {
                                $enumValues = array_map(fn($case) => $case->value ?? $case->name, $enumClass::cases());
                                $ruleArray[] = 'enum:' . implode(',', $enumValues);
                                continue;
                            }
                        } catch (\Throwable) {}
                    }
                    
                    if (is_string($r) && function_exists('enum_exists') && enum_exists($r)) {
                        $enumValues = array_map(fn($case) => $case->value ?? $case->name, $r::cases());
                        $ruleArray[] = 'enum:' . implode(',', $enumValues);
                        continue;
                    }

                    if ($r instanceof \Illuminate\Validation\Rules\In) {
                        try {
                            $refProperty = new \ReflectionProperty($r, 'values');
                            $refProperty->setAccessible(true);
                            $enumValues = $refProperty->getValue($r);
                            $ruleArray[] = 'in:' . implode(',', $enumValues);
                            continue;
                        } catch (\Throwable) {}
                    }

                    $ruleArray[] = is_object($r) ? get_class($r) : (string) $r;
                }
            } else {
                $ruleArray = explode('|', (string) $rule);
                foreach ($ruleArray as $idx => $r) {
                    if (str_starts_with($r, 'enum:')) {
                        $enumClass = substr($r, 5);
                        if (function_exists('enum_exists') && enum_exists($enumClass)) {
                            $enumValues = array_map(fn($case) => $case->value ?? $case->name, $enumClass::cases());
                            $ruleArray[$idx] = 'enum:' . implode(',', $enumValues);
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

            if (!$exists) {
                $endpoint->addParameter(new Parameter(
                    name: $name,
                    type: $type,
                    required: $isRequired,
                    description: null,
                    in: $in,
                    rules: $ruleArray,
                    enumValues: $enumValues
                ));
            }
        }

        $this->introspectModelFromRequest($className, $endpoint, $in);
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

        if (str_contains($rules, 'file') || str_contains($rules, 'image')) {
            return 'file';
        }

        return 'string';
    }

    /**
     * Introspect the database schema and model casts to find undocumented fillable properties.
     *
     * @param string $requestClass
     * @param Endpoint $endpoint
     * @param string $in
     */
    private function introspectModelFromRequest(string $requestClass, Endpoint $endpoint, string $in): void
    {
        $basename = class_basename($requestClass);
        $modelName = str_replace(['Store', 'Update', 'Request'], '', $basename);
        $modelClass = 'App\\Models\\' . $modelName;

        if (!class_exists($modelClass)) {
            return;
        }

        try {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = new $modelClass();
            $table = $model->getTable();
            $fillable = $model->getFillable();

            if (empty($fillable)) {
                return;
            }

            $columns = \Illuminate\Support\Facades\Schema::getColumns($table);
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
                    if (str_contains($dbTypeStr, 'int')) $type = 'integer';
                    elseif (str_contains($dbTypeStr, 'bool') || str_contains($dbTypeStr, 'tinyint(1)')) $type = 'boolean';
                    elseif (str_contains($dbTypeStr, 'json')) $type = 'array';
                    elseif (str_contains($dbTypeStr, 'date')) $type = 'date';
                    elseif (str_contains($dbTypeStr, 'float') || str_contains($dbTypeStr, 'double') || str_contains($dbTypeStr, 'decimal')) $type = 'number';

                    $required = !($dbColumns[$column]['nullable'] ?? true);
                }

                $casts = $model->getCasts();
                if (isset($casts[$column])) {
                    $cast = strtolower((string)$casts[$column]);
                    if (str_contains($cast, 'int')) $type = 'integer';
                    elseif (str_contains($cast, 'bool')) $type = 'boolean';
                    elseif (str_contains($cast, 'array') || str_contains($cast, 'json')) $type = 'array';
                    elseif (str_contains($cast, 'date') || str_contains($cast, 'datetime')) $type = 'date';
                }

                $endpoint->addParameter(new Parameter(
                    name: $column,
                    type: $type,
                    required: $required,
                    description: 'Auto-extracted from ' . $modelName . ' database schema.',
                    in: $in,
                    rules: $required ? ['required'] : [],
                    enumValues: null
                ));
            }
        } catch (\Throwable) {
            // Ignore if DB is not reachable or table doesn't exist
        }
    }
}
