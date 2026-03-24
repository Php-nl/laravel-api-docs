<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Data;

final readonly class Parameter
{
    /**
     * @param string $name
     * @param string $type
     * @param bool $required
     * @param string|null $description
     * @param mixed|null $default
     */
    public function __construct(
        public string $name,
        public string $type,
        public bool $required = true,
        public ?string $description = null,
        public mixed $default = null,
    ) {
    }
}
