<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use PhpNl\LaravelApiDoc\Extraction\DocumentationManager;

final class GenerateResponsesCommand extends Command
{
    /** @var string */
    protected $signature = 'api-doc:generate-responses {--clean : Clear previous responses before generating new ones}';

    /** @var string */
    protected $description = 'Generate realistic example responses by locally executing safe API endpoints.';

    public function handle(DocumentationManager $manager): int
    {
        $this->info('Generating real responses for API endpoints...');

        $endpoints = $manager->get();
        $responsesPath = storage_path('app/api-docs');
        $responsesFile = $responsesPath . '/responses.json';

        if (!File::exists($responsesPath)) {
            File::makeDirectory($responsesPath, 0755, true);
        }

        $existingResponses = [];
        if (!$this->option('clean') && File::exists($responsesFile)) {
            $existingResponses = json_decode(File::get($responsesFile), true) ?: [];
        }

        $appUrl = rtrim(config('app.url', 'http://localhost'), '/');
        $generatedCount = 0;

        foreach ($endpoints as $endpoint) {
            // Only safely execute GET requests that have NO required path parameters to prevent errors
            $methods = array_map('strtoupper', $endpoint['methods']);
            $id = $endpoint['id'];

            if (!in_array('GET', $methods, true)) {
                continue;
            }

            // Check for unfillable required path parameters
            $hasUnfillablePathParams = false;
            foreach ($endpoint['parameters'] as $param) {
                if (($param['in'] ?? '') === 'path' && $param['required']) {
                    $hasUnfillablePathParams = true;
                    break;
                }
            }

            if ($hasUnfillablePathParams) {
                continue;
            }

            $uri = $endpoint['uri'];
            
            // Fill default query parameters
            $queryParams = [];
            foreach ($endpoint['parameters'] as $param) {
                if (($param['in'] ?? '') === 'query') {
                    if (($param['required'] ?? false) || isset($param['default'])) {
                        // Use default or mock based on type
                        if (isset($param['default'])) {
                            $queryParams[$param['name']] = $param['default'];
                        } else {
                            $queryParams[$param['name']] = match($param['type'] ?? 'string') {
                                'integer' => 1,
                                'number' => 1.0,
                                'boolean' => 'true',
                                'array' => '[]',
                                default => 'test',
                            };
                        }
                    }
                }
            }

            $url = $appUrl . '/' . ltrim($uri, '/');
            if (!empty($queryParams)) {
                $url .= '?' . http_build_query($queryParams);
            }

            $this->components->task("GET {$url}", function() use ($url, &$existingResponses, $id, &$generatedCount) {
                try {
                    // Try to fetch it. If the endpoint requires auth, it might return 401. 
                    // That's fine as an example of a "real" failure response.
                    $response = Http::withHeaders(['Accept' => 'application/json'])
                                    ->timeout(3)
                                    ->get($url);

                    if ($response->successful()) {
                        $existingResponses[$id] = $response->json() ?? [];
                        $generatedCount++;
                        return true;
                    }
                } catch (\Exception $e) {
                }
                return false;
            });
        }

        File::put($responsesFile, json_encode($existingResponses, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $this->info("Successfully generated and saved {$generatedCount} real endpoint responses!");

        return self::SUCCESS;
    }
}
