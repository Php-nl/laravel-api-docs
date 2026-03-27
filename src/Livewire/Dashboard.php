<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use PhpNl\LaravelApiDoc\Data\Endpoint;
use PhpNl\LaravelApiDoc\Data\Parameter;
use PhpNl\LaravelApiDoc\Data\Response;

final class Dashboard extends Component
{
    use WithFileUploads;
    /** @var array<int, array<string, mixed>> */
    public array $endpoints = [];

    /** @var array<string, array<string, mixed>> */
    public array $schemas = [];

    /** @var array<string, array<string, mixed>> */
    public array $markdownPages = [];

    /** @var array<string, mixed> */
    public array $realResponses = [];

    /** @var string|null */
    #[\Livewire\Attributes\Url(as: 'endpoint', history: true)]
    public ?string $selectedId = null;

    /** @var string|null */
    #[\Livewire\Attributes\Url(as: 'schema', history: true)]
    public ?string $selectedSchemaId = null;
    
    /** @var string|null */
    #[\Livewire\Attributes\Url(as: 'page', history: true)]
    public ?string $selectedPageId = null;

    /** @var string|null */
    #[\Livewire\Attributes\Url(as: 'version', history: true)]
    public ?string $selectedVersion = null;

    /** @var string */
    public string $search = '';

    /** @var array<string, mixed> */
    public array $tryItOutForm = [];

    /** @var string */
    #[\Livewire\Attributes\Session]
    public string $globalAuthMethod = 'none'; // none, bearer, basic, api_key

    /** @var string */
    #[\Livewire\Attributes\Session]
    public string $globalAuthToken = '';

    /** @var string */
    #[\Livewire\Attributes\Session]
    public string $globalAuthUsername = '';

    /** @var string */
    #[\Livewire\Attributes\Session]
    public string $globalAuthPassword = '';

    /** @var string */
    #[\Livewire\Attributes\Session]
    public string $globalApiKeyName = 'X-API-Key';

    /** @var string */
    #[\Livewire\Attributes\Session]
    public string $globalApiKeyValue = '';

    /** @var string */
    #[\Livewire\Attributes\Session]
    public string $globalApiKeyLocation = 'header'; // header, query

    /** @var bool */
    public bool $useAuth = false;

    /** @var array<string, mixed>|null */
    public ?array $response = null;

    /**
     * Mount the component.
     */
    public function mount(\PhpNl\LaravelApiDoc\Extraction\DocumentationManager $manager): void
    {
        if (config('laravel-api-doc.versions.enabled', false) && !$this->selectedVersion) {
            $this->selectedVersion = config('laravel-api-doc.versions.default', 'v1');
        }

        $data = $manager->get();
        // Fallback for older cached versions
        if (isset($data[0]) && is_array($data[0])) {
            $this->endpoints = $data;
            $globalSchemas = [];
        } else {
            $this->endpoints = $data['endpoints'] ?? [];
            $globalSchemas = $data['schemas'] ?? [];
        }

        $this->schemas = array_merge($globalSchemas, $this->extractSchemas($this->endpoints));
        ksort($this->schemas);
        $this->loadMarkdownPages();

        $responsesFile = storage_path('app/api-docs/responses.json');
        if (\Illuminate\Support\Facades\File::exists($responsesFile)) {
            $this->realResponses = json_decode(\Illuminate\Support\Facades\File::get($responsesFile), true) ?: [];
        }
    }

    private function loadMarkdownPages(): void
    {
        $docsPath = config('laravel-api-doc.ui.docs_path', resource_path('docs/api'));
        
        if (is_dir($docsPath)) {
            foreach (\Illuminate\Support\Facades\File::files($docsPath) as $file) {
                if ($file->getExtension() === 'md') {
                    $pageId = \Illuminate\Support\Str::slug($file->getFilenameWithoutExtension());
                    $title = \Illuminate\Support\Str::title(str_replace(['-', '_'], ' ', $file->getFilenameWithoutExtension()));
                    
                    $this->markdownPages[$pageId] = [
                        'id' => $pageId,
                        'title' => $title,
                        'content' => \Illuminate\Support\Str::markdown($file->getContents()),
                    ];
                }
            }
        }
    }

    /**
     * @param array<int, array<string, mixed>> $endpoints
     * @return array<string, array<string, mixed>>
     */
    private function extractSchemas(array $endpoints): array
    {
        $schemas = [];
        
        foreach ($endpoints as $endpoint) {
            foreach ($endpoint['responses'] ?? [] as $response) {
                if (!empty($response['schema']) && isset($response['schema']['resource'])) {
                    $resourceClass = $response['schema']['resource'];
                    $baseName = class_basename($resourceClass);
                    if (!isset($schemas[$baseName])) {
                        $schemas[$baseName] = $response['schema'];
                    }
                }
                
                // Extract from collection arrays
                if (!empty($response['schema']['items']) && isset($response['schema']['items']['resource'])) {
                    $resourceClass = $response['schema']['items']['resource'];
                    $baseName = class_basename($resourceClass);
                    if (!isset($schemas[$baseName])) {
                        $schemas[$baseName] = $response['schema']['items'];
                    }
                }
            }
        }
        
        ksort($schemas);
        return $schemas;
    }

    /**
     * Go to the home dashboard.
     */
    public function goHome(): void
    {
        $this->selectedId = null;
        $this->selectedSchemaId = null;
        $this->selectedPageId = null;
        $this->response = null;
        $this->tryItOutForm = [
            'query' => [],
            'body' => [],
            'path' => [],
        ];
    }

    /**
     * Select an endpoint.
     */
    public function selectEndpoint(string $id): void
    {
        $this->selectedId = $id;
        $this->selectedSchemaId = null;
        $this->selectedPageId = null;
        $this->response = null;
        $this->tryItOutForm = [
            'query' => [],
            'body' => [],
            'path' => [],
        ];
        
        // Auto-enable authentication for this endpoint if a global method is configured
        $this->useAuth = $this->globalAuthMethod !== 'none';

        $endpoint = $this->getSelectedEndpointProperty();
        if ($endpoint) {
            foreach ($endpoint['parameters'] as $parameter) {
                $in = $parameter['in'] ?? 'query';
                $this->tryItOutForm[$in][$parameter['name']] = $parameter['default'] ?? '';
            }
        }
    }

    /**
     * Run the API request.
     */
    public function runRequest(): void
    {
        $endpoint = $this->getSelectedEndpointProperty();

        if (!$endpoint) {
            return;
        }

        $method = $endpoint['methods'][0];
        $uri = $endpoint['uri'];

        $pathParams = $this->tryItOutForm['path'] ?? [];
        $queryParams = $this->tryItOutForm['query'] ?? [];
        $bodyParams = $this->tryItOutForm['body'] ?? [];

        foreach ($pathParams as $key => $value) {
            $uri = str_replace('{' . $key . '}', (string) $value, $uri);
        }

        $files = [];
        foreach ($bodyParams as $key => $value) {
            if ($value instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                $files[$key] = $value;
                unset($bodyParams[$key]);
            }
        }

        $startTime = microtime(true);

        try {
            $request = \Illuminate\Support\Facades\Http::withHeaders([
                'Accept' => 'application/json',
            ]);

            if ($this->useAuth && $this->globalAuthMethod !== 'none') {
                if ($this->globalAuthMethod === 'bearer' && $this->globalAuthToken !== '') {
                    $request = $request->withToken($this->globalAuthToken);
                } elseif ($this->globalAuthMethod === 'basic' && $this->globalAuthUsername !== '') {
                    $request = $request->withBasicAuth($this->globalAuthUsername, $this->globalAuthPassword);
                } elseif ($this->globalAuthMethod === 'api_key' && $this->globalApiKeyName !== '' && $this->globalApiKeyValue !== '') {
                    if ($this->globalApiKeyLocation === 'header') {
                        $request = $request->withHeaders([
                            $this->globalApiKeyName => $this->globalApiKeyValue,
                        ]);
                    } elseif ($this->globalApiKeyLocation === 'query') {
                        $queryParams[$this->globalApiKeyName] = $this->globalApiKeyValue;
                    }
                }
            }

            if (!empty($queryParams)) {
                $uri .= (str_contains($uri, '?') ? '&' : '?') . http_build_query($queryParams);
            }

            if (!empty($files)) {
                foreach ($files as $name => $file) {
                    $request->attach(
                        $name,
                        file_get_contents($file->getRealPath()),
                        $file->getClientOriginalName()
                    );
                }
                $response = match (strtoupper($method)) {
                    'POST' => $request->post(url($uri), $bodyParams),
                    'PUT' => $request->put(url($uri), $bodyParams),
                    'PATCH' => $request->patch(url($uri), $bodyParams),
                    default => $request->send($method, url($uri)),
                };
            } else {
                $response = match (strtoupper($method)) {
                    'GET' => $request->get(url($uri)),
                    'POST' => $request->post(url($uri), $bodyParams),
                    'PUT' => $request->put(url($uri), $bodyParams),
                    'PATCH' => $request->patch(url($uri), $bodyParams),
                    'DELETE' => $request->delete(url($uri), $bodyParams),
                    default => $request->send($method, url($uri), ['json' => $bodyParams]),
                };
            }

            $this->response = [
                'status' => $response->status(),
                'duration' => round((microtime(true) - $startTime) * 1000, 2),
                'headers' => $response->headers(),
                'body' => $response->json() ?? $response->body(),
            ];
        } catch (\Exception $e) {
            $this->response = [
                'status' => 500,
                'duration' => round((microtime(true) - $startTime) * 1000, 2),
                'body' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * Get the selected endpoint.
     */
    public function getSelectedEndpointProperty(): ?array
    {
        return collect($this->endpoints)->first(fn (array $e) => ($e['id'] ?? null) === $this->selectedId);
    }

    /**
     * Select a schema view instead of an endpoint.
     */
    public function selectSchema(string $id): void
    {
        $this->selectedSchemaId = $id;
        $this->selectedId = null;
        $this->selectedPageId = null;
        $this->response = null;
        $this->tryItOutForm = [];
    }

    /**
     * Select a markdown page.
     */
    public function selectPage(string $id): void
    {
        $this->selectedPageId = $id;
        $this->selectedId = null;
        $this->selectedSchemaId = null;
        $this->response = null;
        $this->tryItOutForm = [];
    }

    /**
     * Get the selected schema.
     */
    public function getSelectedSchemaProperty(): ?array
    {
        if (!$this->selectedSchemaId) {
            return null;
        }
        return $this->schemas[$this->selectedSchemaId] ?? null;
    }

    /**
     * Get the selected markdown page.
     */
    public function getSelectedPageProperty(): ?array
    {
        if (!$this->selectedPageId) {
            return null;
        }
        return $this->markdownPages[$this->selectedPageId] ?? null;
    }

    /**
     * Get the filtered endpoints.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getGroupsProperty(): array
    {
        $versionEnabled = config('laravel-api-doc.versions.enabled', false);

        return collect($this->endpoints)
            ->filter(fn (array $e) => str_contains(strtolower($e['uri']), strtolower($this->search)))
            ->filter(function (array $e) use ($versionEnabled) {
                if (!$versionEnabled || !$this->selectedVersion) return true;
                
                return str_contains($e['uri'], '/' . $this->selectedVersion . '/') || 
                       str_starts_with($e['uri'], $this->selectedVersion . '/');
            })
            ->groupBy(fn (array $e) => $e['group'] ?? 'Default')
            ->toArray();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('laravel-api-doc::livewire.dashboard')
            ->layout('laravel-api-doc::layout');
    }
}
