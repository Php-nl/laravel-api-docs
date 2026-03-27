<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use PhpNl\LaravelApiDoc\Commands\CacheApiDocCommand;
use PhpNl\LaravelApiDoc\Commands\ClearApiDocCommand;
use PhpNl\LaravelApiDoc\Commands\GenerateOpenApiCommand;
use PhpNl\LaravelApiDoc\Commands\GenerateResponsesCommand;
use PhpNl\LaravelApiDoc\Livewire\Dashboard;

final class LaravelApiDocServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-api-doc.php',
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
                __DIR__.'/../config/laravel-api-doc.php' => config_path('laravel-api-doc.php'),
            ], 'laravel-api-doc-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-api-doc'),
            ], 'laravel-api-doc-views');

            $this->commands([
                CacheApiDocCommand::class,
                ClearApiDocCommand::class,
                GenerateOpenApiCommand::class,
                GenerateResponsesCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-api-doc');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'api-doc');

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
