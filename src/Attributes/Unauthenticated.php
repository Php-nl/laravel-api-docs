<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Unauthenticated
{
    // Indicates this route or controller does not require authentication.
}
