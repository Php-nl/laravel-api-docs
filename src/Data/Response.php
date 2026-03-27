<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Data;

final readonly class Response
{
    /**
     * @param  array<string, mixed>|null  $schema
     */
    public function __construct(
        public int $status,
        public ?string $description = null,
        public ?array $schema = null,
    ) {}
}
