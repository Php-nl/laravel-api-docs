<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use PhpNl\LaravelApiDoc\Extraction\DocumentationManager;
use PhpNl\LaravelApiDoc\Livewire\Dashboard;
use PhpNl\LaravelApiDoc\Services\OpenApiGenerator;

Route::group([
    'prefix' => Config::get('laravel-api-doc.ui.path', '/docs/api'),
    'middleware' => Config::get('laravel-api-doc.ui.middleware', ['web']),
], function () {
    Route::get('/', Dashboard::class)
        ->name('laravel-api-doc.dashboard');

    Route::get('openapi.json', function (DocumentationManager $manager, OpenApiGenerator $openApi) {
        return response()->json($openApi->generate($manager->get()));
    })->name('laravel-api-doc.json');
});
