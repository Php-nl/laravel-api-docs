<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use PhpNl\LaravelApiDoc\Attributes\ExcludeFromDocs;

final readonly class RouteExplorer
{
    public function __construct(
        private Router $router,
    ) {}

    /**
     * Explore and filter routes based on configuration.
     *
     * @return array<int, Route>
     */
    public function explore(): array
    {
        $includePatterns = Config::get('laravel-api-doc.routes.include', ['api/*']);
        $excludePatterns = Config::get('laravel-api-doc.routes.exclude', []);
        $excludeNames = Config::get('laravel-api-doc.routes.exclude_names', []);
        $excludeMiddleware = Config::get('laravel-api-doc.routes.exclude_middleware', []);

        return array_filter(
            $this->router->getRoutes()->getRoutes(),
            fn (Route $route) => $this->shouldInclude($route, $includePatterns, $excludePatterns, $excludeNames, $excludeMiddleware)
        );
    }

    /**
     * Determine if a route should be included in the documentation.
     *
     * @param  array<int, string>  $includePatterns
     * @param  array<int, string>  $excludePatterns
     * @param  array<int, string>  $excludeNames
     * @param  array<int, string>  $excludeMiddleware
     */
    private function shouldInclude(Route $route, array $includePatterns, array $excludePatterns, array $excludeNames, array $excludeMiddleware): bool
    {
        $uri = $route->uri();
        $name = $route->getName() ?? '';

        $included = false;
        foreach ($includePatterns as $pattern) {
            if (Str::is($pattern, $uri)) {
                $included = true;
                break;
            }
        }

        if (! $included) {
            return false;
        }

        foreach ($excludePatterns as $pattern) {
            if (Str::is($pattern, $uri)) {
                return false;
            }
        }

        if ($name) {
            foreach ($excludeNames as $pattern) {
                if (Str::is($pattern, $name)) {
                    return false;
                }
            }
        }

        $routeMiddleware = $route->gatherMiddleware();
        foreach ($excludeMiddleware as $middleware) {
            if (in_array($middleware, $routeMiddleware, true)) {
                return false;
            }
        }

        if ($this->hasExcludeAttribute($route)) {
            return false;
        }

        return true;
    }

    private function hasExcludeAttribute(Route $route): bool
    {
        $action = $route->getAction();

        if (! isset($action['controller']) || ! is_string($action['controller'])) {
            return false;
        }

        if (str_contains($action['controller'], '@')) {
            [$controller, $method] = explode('@', $action['controller']);
        } else {
            $controller = $action['controller'];
            $method = '__invoke';
        }

        if (! class_exists($controller) || ! method_exists($controller, $method)) {
            return false;
        }

        try {
            $reflectionClass = new \ReflectionClass($controller);
            if (! empty($reflectionClass->getAttributes(ExcludeFromDocs::class))) {
                return true;
            }

            $reflectionMethod = $reflectionClass->getMethod($method);
            if (! empty($reflectionMethod->getAttributes(ExcludeFromDocs::class))) {
                return true;
            }
        } catch (\ReflectionException) {
            // Ignore reflection errors safely
        }

        return false;
    }
}
