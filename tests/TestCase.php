<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Tests;

use Illuminate\Foundation\Application;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use PhpNl\LaravelApiDoc\LaravelApiDocServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * Get the package providers.
     *
     * @param  Application  $app
     * @return array<int, string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            LaravelApiDocServiceProvider::class,
        ];
    }

    /**
     * Define the environment.
     *
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('laravel-api-doc.routes.include', ['api/*']);
    }
}
