<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class ExcludeFromDocs
{
    // Indicates this route or controller should be ignored during API documentation extraction.
}
