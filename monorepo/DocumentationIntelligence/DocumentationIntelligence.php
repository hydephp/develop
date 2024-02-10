<?php

/**
 * @internal This script is part of the internal monorepo tools.
 */

declare(strict_types=1);

use Hyde\Foundation\HydeKernel;
use Hyde\Markdown\Models\MarkdownDocument;

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../packages/hydefront/.github/scripts/minima.php';

define('TIME_START', microtime(true));
define('BASE_PATH', realpath(__DIR__.'/../../'));
define('OUTPUT_PATH', __DIR__.'/results');

Command::main(function () {
    /** @var Command $this */
    $this->info('Generating documentation intelligence...');
    $this->line();

    $generator = new DocumentationIntelligence();

    task('discover pages', fn () => $generator->discoverPages());
    task('assemble model', fn () => $generator->assembleModel());

    $this->line();
    $this->info(sprintf("Time taken: %s",
        number_format((microtime(true) - TIME_START) * 1000, 2) . 'ms',
    ));

    return 0;
});

class DocumentationIntelligence
{
    protected HydeKernel $kernel;

    /** @var array<string, \Hyde\Markdown\Models\MarkdownDocument> */
    protected array $pages = [];

    public function __construct()
    {
        $this->kernel = new HydeKernel(BASE_PATH);

        HydeKernel::setInstance($this->kernel);
    }

    public function discoverPages(): void
    {
        $files = glob(BASE_PATH.'/docs/**/*.md');

        foreach ($files as $file) {
            $filepath = str_replace(BASE_PATH.'/docs/', '', $file);
            $this->pages[$filepath] = MarkdownDocument::parse($file);
        }
    }

    public function assembleModel(): void
    {
        // This script generates a single .txt model of all HydePHP documentation

        $model = sprintf("Start HydePHP Documentation (Framework version v%s)\n\n", HydeKernel::VERSION);

        foreach ($this->pages as $path => $page) {
            $model .= sprintf("--- %s ---\n\n%s\n\n", $path, $page->markdown);
        }

        file_put_contents(OUTPUT_PATH.'/model.txt', $model);
    }
}
