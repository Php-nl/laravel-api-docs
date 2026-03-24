<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Livewire;

use Livewire\Component;
use PhpNl\LaravelApiDoc\Data\Endpoint;
use PhpNl\LaravelApiDoc\Data\Parameter;
use PhpNl\LaravelApiDoc\Data\Response;

final class Dashboard extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $endpoints = [];

    /** @var string|null */
    public ?string $selectedId = null;

    /** @var string */
    public string $search = '';

    /** @var array<string, mixed> */
    public array $tryItOutForm = [];

    /** @var string */
    public string $globalAuthMethod = 'none'; // none, bearer, basic, api_key

    /** @var string */
    public string $globalAuthToken = '';

    /** @var string */
    public string $globalAuthUsername = '';

    /** @var string */
    public string $globalAuthPassword = '';

    /** @var string */
    public string $globalApiKeyName = 'X-API-Key';

    /** @var string */
    public string $globalApiKeyValue = '';

    /** @var string */
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
        $this->endpoints = $manager->get();
    }

    /**
     * Select an endpoint.
     */
    public function selectEndpoint(string $id): void
    {
        $this->selectedId = $id;
        $this->response = null;
        $this->tryItOutForm = [];
        
        // Auto-enable authentication for this endpoint if a global method is configured
        $this->useAuth = $this->globalAuthMethod !== 'none';

        $endpoint = $this->getSelectedEndpointProperty();
        if ($endpoint) {
            foreach ($endpoint['parameters'] as $parameter) {
                $this->tryItOutForm[$parameter['name']] = $parameter['default'] ?? '';
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

        $form = $this->tryItOutForm;

        foreach ($form as $key => $value) {
            if (str_contains($uri, '{' . $key . '}')) {
                $uri = str_replace('{' . $key . '}', (string) $value, $uri);
                unset($form[$key]);
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
                        $uri .= (str_contains($uri, '?') ? '&' : '?') . urlencode($this->globalApiKeyName) . '=' . urlencode($this->globalApiKeyValue);
                    }
                }
            }

            $response = $request->send($method, url($uri), [
                'json' => $form,
            ]);

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
     * Get the filtered endpoints.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getGroupsProperty(): array
    {
        return collect($this->endpoints)
            ->filter(fn (array $e) => str_contains(strtolower($e['uri']), strtolower($this->search)))
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
