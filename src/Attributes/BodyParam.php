<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class BodyParam
{
    public function __construct(
        public string $name,
        public string $type = 'string',
        public ?string $description = null,
        public bool $required = false,
        public ?string $example = null,
        public array $enumValues = [],
    ) {}
}
