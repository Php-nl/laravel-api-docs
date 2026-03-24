<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction\Support;

use Illuminate\Database\Eloquent\Model;

final class DummyResourceModel extends Model
{
    /** @var array<int, string> */
    protected $guarded = [];

    /**
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key): mixed
    {
        if (str_ends_with($key, '_id') || $key === 'id') {
            return 1;
        }

        if (str_starts_with($key, 'is_') || str_starts_with($key, 'has_')) {
            return true;
        }

        if (str_ends_with($key, '_at')) {
            return now()->toIso8601String();
        }

        return $key . '_string';
    }

    /**
     * @param string $key
     * @return bool
     */
    public function relationLoaded($key): bool
    {
        return false;
    }

    /**
     * @param string $method
     * @param array<int, mixed> $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return new self();
    }
}
