<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

final class EloquentSchemaReader
{
    /**
     * @param  class-string<Model>  $modelClass
     * @return array<string, mixed>
     */
    public function read(string $modelClass): array
    {
        if (! class_exists($modelClass)) {
            return [];
        }

        try {
            /** @var Model $model */
            $model = new $modelClass;
            $table = $model->getTable();

            // Requires doctrine/dbal or Laravel 11's native Schema::getColumns()
            $columns = Schema::getColumns($table);

            $hidden = $model->getHidden();
            $casts = $model->getCasts();
            // Dates are handled by casts usually in modern Laravel, but just in case
            $dates = $model->getDates();

            $schema = [];

            foreach ($columns as $column) {
                $name = $column['name'];

                if (in_array($name, $hidden, true)) {
                    continue;
                }

                $type = $this->mapDatabaseType($column['type_name']);

                // Override with cast if exists
                if (isset($casts[$name])) {
                    $type = $this->mapCastType($casts[$name]);
                } elseif (in_array($name, $dates, true)) {
                    $type = 'string'; // dates are strings in JSON
                }

                $schema[$name] = [
                    'type' => $type,
                    'example' => $this->generateExample($name, $type),
                ];
            }

            return $schema;
        } catch (\Throwable) {
            return [];
        }
    }

    private function mapDatabaseType(string $dbType): string
    {
        $dbType = strtolower($dbType);

        if (str_contains($dbType, 'int')) {
            return 'number';
        }
        if (str_contains($dbType, 'float') || str_contains($dbType, 'decimal') || str_contains($dbType, 'double')) {
            return 'number';
        }
        if (str_contains($dbType, 'bool') || str_contains($dbType, 'tinyint(1)')) {
            return 'boolean';
        }
        if (str_contains($dbType, 'json')) {
            return 'object';
        }

        return 'string';
    }

    private function mapCastType(string $cast): string
    {
        return match (strtolower($cast)) {
            'int', 'integer' => 'number',
            'real', 'float', 'double', 'decimal' => 'number',
            'string' => 'string',
            'bool', 'boolean' => 'boolean',
            'object', 'array', 'json', 'collection' => 'object',
            'date', 'datetime', 'immutable_date', 'immutable_datetime' => 'string',
            default => 'string',
        };
    }

    private function generateExample(string $name, string $type): mixed
    {
        if ($name === 'id' || str_ends_with($name, '_id')) {
            return 1;
        }

        if ($type === 'number') {
            return 100;
        }
        if ($type === 'boolean') {
            return true;
        }
        if ($type === 'object') {
            return [];
        }

        if (str_contains($name, 'email')) {
            return 'user@example.com';
        }
        if (str_contains($name, 'url')) {
            return 'https://example.com';
        }

        return $name.'_string';
    }
}
