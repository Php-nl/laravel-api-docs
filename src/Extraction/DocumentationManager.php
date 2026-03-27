<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PhpNl\LaravelApiDoc\Data\Endpoint;
use PhpNl\LaravelApiDoc\Data\Parameter;
use PhpNl\LaravelApiDoc\Data\Response;
use PhpNl\LaravelApiDoc\Extraction\Webhooks\WebhookExtractor;

final readonly class DocumentationManager
{
    public function __construct(
        private Generator $generator,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        return Cache::rememberForever('laravel-api-doc.documentation', function () {
            return $this->fresh();
        });
    }

    public function cache(): void
    {
        Cache::forever('laravel-api-doc.documentation', $this->fresh());
    }

    public function clear(): void
    {
        Cache::forget('laravel-api-doc.documentation');
    }

    /**
     * @return array<string, mixed>
     */
    private function fresh(): array
    {
        SchemaRegistry::clear();

        $endpoints = array_map(
            fn (Endpoint $endpoint) => $this->endpointToArray($endpoint),
            $this->generator->generate()
        );

        $webhooksConfig = config('laravel-api-doc.webhooks', []);
        if (! empty($webhooksConfig)) {
            $webhookExtractor = new WebhookExtractor;
            $webhooks = array_map(
                fn (Endpoint $endpoint) => $this->endpointToArray($endpoint),
                $webhookExtractor->extract($webhooksConfig)
            );
            $endpoints = array_merge($endpoints, $webhooks);
        }

        return [
            'endpoints' => $endpoints,
            'schemas' => SchemaRegistry::all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function endpointToArray(Endpoint $endpoint): array
    {
        $methodPrefix = strtolower(implode('-', $endpoint->methods));
        $uriSlug = Str::slug(str_replace('/', '-', $endpoint->uri));
        $id = trim($methodPrefix.'-'.$uriSlug, '-');

        return [
            'id' => $id,
            'uri' => $endpoint->uri,
            'methods' => $endpoint->methods,
            'name' => $endpoint->name,
            'group' => $endpoint->group,
            'description' => $endpoint->description,
            'auth_required' => $endpoint->authRequired,
            'parameters' => array_map(fn (Parameter $p) => [
                'name' => $p->name,
                'in' => $p->in,
                'type' => $p->type,
                'required' => $p->required,
                'description' => $p->description,
                'default' => $p->default,
                'rules' => $p->rules,
                'enumValues' => $p->enumValues,
            ], $endpoint->parameters),
            'responses' => array_map(fn (Response $r) => [
                'status' => $r->status,
                'description' => $r->description,
                'schema' => $r->schema,
            ], $endpoint->responses),
        ];
    }
}
