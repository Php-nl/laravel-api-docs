<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction;

use Illuminate\Support\Facades\Config;
use PhpNl\LaravelApiDoc\Data\Endpoint;
use PhpNl\LaravelApiDoc\Extraction\Extractors\AttributeExtractor;
use PhpNl\LaravelApiDoc\Extraction\Extractors\DocblockExtractor;
use PhpNl\LaravelApiDoc\Extraction\Extractors\Extractor;
use PhpNl\LaravelApiDoc\Extraction\Extractors\FormRequestExtractor;
use PhpNl\LaravelApiDoc\Extraction\Extractors\JsonResourceExtractor;
use PhpNl\LaravelApiDoc\Extraction\Extractors\SignatureExtractor;

final readonly class Generator
{
    /**
     * @param RouteExplorer $explorer
     */
    public function __construct(
        private RouteExplorer $explorer,
    ) {
    }

    /**
     * Generate the documentation endpoints.
     *
     * @return array<int, Endpoint>
     */
    public function generate(): array
    {
        $routes = $this->explorer->explore();
        $endpoints = [];

        foreach ($routes as $route) {
            $endpoint = new Endpoint(
                uri: $route->uri(),
                methods: $route->methods(),
            );

            foreach ($this->getExtractors() as $extractor) {
                $extractor->extract($route, $endpoint);
            }

            $endpoints[] = $endpoint;
        }

        return $endpoints;
    }

    /**
     * Get the enabled extractors.
     *
     * @return array<int, Extractor>
     */
    private function getExtractors(): array
    {
        $extractors = [];

        if (Config::get('laravel-api-doc.extractors.attributes', true)) {
            $extractors[] = new AttributeExtractor();
        }

        if (Config::get('laravel-api-doc.extractors.signatures', true)) {
            $extractors[] = new SignatureExtractor();
        }

        if (Config::get('laravel-api-doc.extractors.docblocks', true)) {
            $extractors[] = new DocblockExtractor();
        }

        if (Config::get('laravel-api-doc.extractors.form_requests', true)) {
            $extractors[] = new FormRequestExtractor();
        }

        if (Config::get('laravel-api-doc.extractors.json_resources', true)) {
            $extractors[] = new JsonResourceExtractor();
        }

        return $extractors;
    }
}
