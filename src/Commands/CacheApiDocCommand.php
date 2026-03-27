<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Commands;

use Illuminate\Console\Command;
use PhpNl\LaravelApiDoc\Extraction\DocumentationManager;

final class CacheApiDocCommand extends Command
{
    /** @var string */
    protected $signature = 'api-doc:cache';

    /** @var string */
    protected $description = 'Generate and cache the API documentation.';

    public function handle(DocumentationManager $manager): int
    {
        $this->info('Generating API documentation...');

        $manager->cache();

        $this->info('API documentation cached successfully!');

        return self::SUCCESS;
    }
}
