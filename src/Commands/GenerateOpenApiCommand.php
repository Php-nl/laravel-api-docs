<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PhpNl\LaravelApiDoc\Extraction\DocumentationManager;
use PhpNl\LaravelApiDoc\Services\OpenApiGenerator;

final class GenerateOpenApiCommand extends Command
{
    /** @var string */
    protected $signature = 'api-doc:openapi {--output= : The file path to output the JSON to}';

    /** @var string */
    protected $description = 'Generate and export the OpenAPI specification.';

    public function handle(DocumentationManager $manager, OpenApiGenerator $generator): int
    {
        $this->info('Parsing endpoints and generating OpenAPI schema...');

        $endpoints = $manager->get();
        $schema = $generator->generate($endpoints);

        $json = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if (! $json) {
            $this->error('Failed to encode OpenAPI schema to JSON.');

            return self::FAILURE;
        }

        $outputPath = $this->option('output') ?: base_path('openapi.json');

        File::put($outputPath, $json);

        $this->info('OpenAPI documentation exported successfully to: '.$outputPath);

        return self::SUCCESS;
    }
}
