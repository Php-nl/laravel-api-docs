<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Data;

final class Endpoint
{
    /**
     * @param  array<int, string>  $methods
     * @param  array<int, Parameter>  $parameters
     * @param  array<int, Response>  $responses
     */
    public function __construct(
        public string $uri,
        public array $methods,
        public ?string $name = null,
        public ?string $group = null,
        public ?string $description = null,
        public array $parameters = [],
        public array $responses = [],
        public bool $authRequired = false,
    ) {}

    /**
     * Add a parameter to the endpoint.
     */
    public function addParameter(Parameter $parameter): void
    {
        $this->parameters[] = $parameter;
    }

    /**
     * Add a response to the endpoint.
     */
    public function addResponse(Response $response): void
    {
        $this->responses[] = $response;
    }
}
