<?php

declare(strict_types=1);

namespace PhpNl\LaravelApiDoc\Commands;

use Illuminate\Console\Command;
use PhpNl\LaravelApiDoc\Extraction\DocumentationManager;

final class ClearApiDocCommand extends Command
{
    /** @var string */
    protected $signature = 'api-doc:clear';

    /** @var string */
    protected $description = 'Clear the cached API documentation.';

    public function handle(DocumentationManager $manager): int
    {
        $manager->clear();

        $this->info('API documentation cache cleared successfully!');

        return self::SUCCESS;
    }
}
