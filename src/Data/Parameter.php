<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Data;

final readonly class Parameter
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $required = true,
        public ?string $description = null,
        public mixed $default = null,
        public string $in = 'query',
        public array $rules = [],
        public ?array $enumValues = null,
    ) {}
}
