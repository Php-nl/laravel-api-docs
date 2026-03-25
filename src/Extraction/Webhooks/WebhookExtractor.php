<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction\Webhooks;

use Illuminate\Http\Resources\Json\JsonResource;
use PhpNl\LaravelApiDoc\Data\Endpoint;
use PhpNl\LaravelApiDoc\Data\Response;
use PhpNl\LaravelApiDoc\Extraction\Support\DummyResourceModel;
use Throwable;

final readonly class WebhookExtractor
{
    /**
     * @param array<string, string> $webhooks Configured webhooks mapping [event_name => ResourceClass]
     * @return array<int, Endpoint>
     */
    public function extract(array $webhooks): array
    {
        $endpoints = [];

        foreach ($webhooks as $event => $resourceClass) {
            $endpoint = new Endpoint(
                uri: $event,
                methods: ['WEBHOOK'],
                name: \Illuminate\Support\Str::title(str_replace(['.', '_', '-'], ' ', $event)),
                group: 'Webhooks',
                description: "Outgoing webhook payload for event: `{$event}`",
                authRequired: false
            );

            if (class_exists($resourceClass) && is_subclass_of($resourceClass, JsonResource::class)) {
                $endpoint->addResponse(new Response(
                    status: 200,
                    description: "Webhook Payload Structure",
                    schema: $this->extractSchema($resourceClass)
                ));
            }

            $endpoints[] = $endpoint;
        }

        return $endpoints;
    }

    /**
     * @param string $className
     * @return array<string, mixed>
     */
    private function extractSchema(string $className): array
    {
        try {
            /** @var JsonResource $resource */
            $resource = new $className(new DummyResourceModel());
            $payload = $resource->toArray(request());

            return [
                'type' => 'object',
                'resource' => $className,
                'properties' => $this->mapArrayToSchema($payload),
            ];
        } catch (Throwable) {
            return [
                'type' => 'object',
                'resource' => $className,
            ];
        }
    }

    /**
     * @param array<int|string, mixed> $payload
     * @return array<string, mixed>
     */
    private function mapArrayToSchema(array $payload): array
    {
        $schema = [];
        foreach ($payload as $key => $value) {
            $type = gettype($value);
            if ($value instanceof JsonResource) {
                $schema[$key] = [
                    'type' => 'object',
                    'properties' => $this->mapArrayToSchema($value->toArray(request())),
                ];
                continue;
            }
            if ($type === 'array') {
                if (array_is_list($value)) {
                    $itemType = count($value) > 0 ? gettype($value[0]) : 'mixed';
                    if ($itemType === 'object' && $value[0] instanceof JsonResource) {
                        $schema[$key] = [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => $this->mapArrayToSchema($value[0]->toArray(request())),
                            ],
                        ];
                        continue;
                    }
                    $schema[$key] = [
                        'type' => 'array',
                        'items' => [
                            'type' => $itemType === 'array' ? 'object' : $itemType,
                            'properties' => $itemType === 'array' ? $this->mapArrayToSchema($value[0] ?? []) : null,
                        ],
                    ];
                } else {
                    $schema[$key] = [
                        'type' => 'object',
                        'properties' => $this->mapArrayToSchema($value),
                    ];
                }
                continue;
            }
            if (is_object($value)) {
                if (method_exists($value, '__toString')) {
                    $value = (string) $value;
                } elseif ($value instanceof \JsonSerializable) {
                    $value = json_encode($value);
                } else {
                    $value = get_class($value);
                }
                $type = 'string';
            }
            $schema[$key] = [
                'type' => match ($type) {
                    'integer', 'double' => 'number',
                    'boolean' => 'boolean',
                    default => 'string',
                },
                'example' => $value,
            ];
        }
        return $schema;
    }
}
