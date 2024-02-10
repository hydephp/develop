<?php

declare(strict_types=1);

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../packages/hydefront/.github/scripts/minima.php';

define('TIME_START', microtime(true));

Command::main(function () {
    /** @var Command $this */
    $this->info('Generating documentation intelligence...');
    $this->line();

    $generator = new DocumentationIntelligence();

    task('discover pages', function () use ($generator) {
        $generator->discoverPages();
    });

    $this->line();
    $this->info('Time taken: '.round((microtime(true) - TIME_START) * 1000, 2).'ms');
    return 0;
});

/**
 * @internal
 */
class DocumentationIntelligence
{
    /** @var array<string, \Hyde\Pages\DocumentationPage> */
    protected array $pages = [];

    public function discoverPages(): void
    {
        //
    }
}
