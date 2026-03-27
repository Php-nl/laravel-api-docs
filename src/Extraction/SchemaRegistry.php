<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction;

final class SchemaRegistry
{
    private static array $schemas = [];

    public static function register(string $name, array $schema): void
    {
        self::$schemas[$name] = $schema;
    }

    public static function has(string $name): bool
    {
        return isset(self::$schemas[$name]);
    }

    public static function get(string $name): ?array
    {
        return self::$schemas[$name] ?? null;
    }

    public static function all(): array
    {
        ksort(self::$schemas);
        return self::$schemas;
    }

    public static function clear(): void
    {
        self::$schemas = [];
    }
}
