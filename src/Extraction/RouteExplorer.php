<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Extraction;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

final readonly class RouteExplorer
{
    /**
     * @param Router $router
     */
    public function __construct(
        private Router $router,
    ) {
    }

    /**
     * Explore and filter routes based on configuration.
     *
     * @return array<int, Route>
     */
    public function explore(): array
    {
        $includePatterns = Config::get('laravel-api-doc.routes.include', ['api/*']);
        $excludePatterns = Config::get('laravel-api-doc.routes.exclude', []);

        return array_filter(
            $this->router->getRoutes()->getRoutes(),
            fn (Route $route) => $this->shouldInclude($route, $includePatterns, $excludePatterns)
        );
    }

    /**
     * Determine if a route should be included in the documentation.
     *
     * @param Route $route
     * @param array<int, string> $includePatterns
     * @param array<int, string> $excludePatterns
     * @return bool
     */
    private function shouldInclude(Route $route, array $includePatterns, array $excludePatterns): bool
    {
        $uri = $route->uri();

        $included = false;
        foreach ($includePatterns as $pattern) {
            if (Str::is($pattern, $uri)) {
                $included = true;
                break;
            }
        }

        if (!$included) {
            return false;
        }

        foreach ($excludePatterns as $pattern) {
            if (Str::is($pattern, $uri)) {
                return false;
            }
        }

        return true;
    }
}
