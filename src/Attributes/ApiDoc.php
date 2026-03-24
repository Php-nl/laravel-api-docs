<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final readonly class ApiDoc
{
    /**
     * @param string|null $name
     * @param string|null $group
     * @param string|null $description
     */
    public function __construct(
        public ?string $name = null,
        public ?string $group = null,
        public ?string $description = null,
    ) {
    }
}
