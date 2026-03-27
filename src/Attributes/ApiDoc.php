<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class ApiDoc
{
    public function __construct(
        public ?string $name = null,
        public ?string $group = null,
        public ?string $description = null,
    ) {}
}
