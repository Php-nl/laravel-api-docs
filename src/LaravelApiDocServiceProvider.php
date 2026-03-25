<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use PhpNl\LaravelApiDoc\Livewire\Dashboard;

final class LaravelApiDocServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-api-doc.php',
            'laravel-api-doc'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel-api-doc.php' => config_path('laravel-api-doc.php'),
            ], 'laravel-api-doc-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-api-doc'),
            ], 'laravel-api-doc-views');

            $this->commands([
                \PhpNl\LaravelApiDoc\Commands\CacheApiDocCommand::class,
                \PhpNl\LaravelApiDoc\Commands\ClearApiDocCommand::class,
                \PhpNl\LaravelApiDoc\Commands\GenerateOpenApiCommand::class,
                \PhpNl\LaravelApiDoc\Commands\GenerateResponsesCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-api-doc');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        \Illuminate\Support\Facades\Blade::anonymousComponentPath(__DIR__ . '/../resources/views/components', 'api-doc');

        $this->registerLivewireComponents();
    }

    /**
     * Register Livewire components.
     */
    private function registerLivewireComponents(): void
    {
        Livewire::component('laravel-api-doc::dashboard', Dashboard::class);
    }
}
