<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Data;

final class Endpoint
{
    /**
     * @param string $uri
     * @param array<int, string> $methods
     * @param string|null $name
     * @param string|null $group
     * @param string|null $description
     * @param array<int, Parameter> $parameters
     * @param array<int, Response> $responses
     */
    public function __construct(
        public string $uri,
        public array $methods,
        public ?string $name = null,
        public ?string $group = null,
        public ?string $description = null,
        public array $parameters = [],
        public array $responses = [],
    ) {
    }

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
