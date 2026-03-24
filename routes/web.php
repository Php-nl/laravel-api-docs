<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use PhpNl\LaravelApiDoc\Livewire\Dashboard;

Route::group([
    'prefix' => Config::get('laravel-api-doc.ui.path', '/docs/api'),
    'middleware' => Config::get('laravel-api-doc.ui.middleware', ['web']),
], function () {
    Route::get('/', Dashboard::class)
        ->name('laravel-api-doc.dashboard');
});
