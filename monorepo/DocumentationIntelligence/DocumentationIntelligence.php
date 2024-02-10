<?php

/**
 * @internal This script is part of the internal monorepo tools.
 */

declare(strict_types=1);

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Application;

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

class DocumentationIntelligence
{
    protected HydeKernel $kernel;
    protected Application $app;

    /** @var array<string, \Hyde\Pages\DocumentationPage> */
    protected array $pages = [];

    public function __construct()
    {
        $this->kernel = new HydeKernel(realpath(__DIR__.'/../../'));
        $this->app = require_once __DIR__.'/../../app/bootstrap.php';

        HydeKernel::setInstance($this->kernel);
    }

    public function discoverPages(): void
    {
        //
    }
}
