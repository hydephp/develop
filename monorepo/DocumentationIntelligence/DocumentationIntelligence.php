<?php

/**
 * @internal This script is part of the internal monorepo tools.
 */

declare(strict_types=1);

use Illuminate\Support\Str;
use Hyde\Foundation\HydeKernel;
use Hyde\Markdown\Models\MarkdownDocument;

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../packages/hydefront/.github/scripts/minima.php';

define('TIME_START', microtime(true));
define('BASE_PATH', realpath(__DIR__.'/../../'));
define('OUTPUT_PATH', realpath(__DIR__.'/results'));

Command::main(function () {
    /** @var Command $this */
    $this->info('Generating documentation intelligence...');
    $this->line();

    $generator = new DocumentationIntelligence();

    task('discover pages', fn () => $generator->discoverPages());
    task('assemble model', fn () => $generator->assembleModel());
    task('create pruned model', fn () => $generator->createPrunedModel());

    task('get data', function () use (&$data) {
        $data = [
            number_format(filesize(OUTPUT_PATH.'/model.txt') / 1024, 2).'KB',
            number_format(str_word_count(file_get_contents(OUTPUT_PATH.'/model.txt'))),
            number_format(count(file(OUTPUT_PATH.'/model.txt')) + 1),

            number_format(filesize(OUTPUT_PATH.'/model-pruned.txt') / 1024, 2).'KB',
            number_format(str_word_count(file_get_contents(OUTPUT_PATH.'/model-pruned.txt'))),
            number_format(count(file(OUTPUT_PATH.'/model-pruned.txt')) + 1),
            number_format((1 - (filesize(OUTPUT_PATH.'/model-pruned.txt') / filesize(OUTPUT_PATH.'/model.txt'))) * 100, 2),
        ];
    });

    $this->line();

    $this->line(sprintf(<<<'EOF'
        Full model details:
            Model size: %s
            Model words: %s
            Model lines: %s
            
        Pruned model details:
            Model size: %s
            Model words: %s
            Model lines: %s
            Pruned model compression: %s%%
        EOF,
        ...$data
    ));

    $this->line();
    $this->info(sprintf('Time taken: %s. Memory used: %s',
        number_format((microtime(true) - TIME_START) * 1000, 2).'ms',
        number_format(memory_get_peak_usage() / 1024 / 1024, 2).'MB'
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

        $model = str_replace("\r\n", "\n", $model);

        file_put_contents(OUTPUT_PATH.'/model.txt', $model);
    }

    public function createPrunedModel(): void
    {
        // Remove all code blocks from the full model

        $model = file_get_contents(OUTPUT_PATH.'/model.txt');

        // Remove all code blocks
        $model = preg_replace('/```.*?```/s', '', $model);
        $model = preg_replace('/<pre>.*?<\/pre>/s', '', $model);

        $needles = ['<!-- ', '[Blade]: ', '--- redirects/', '<meta http-equiv="refresh" ', 'Redirecting you to ['];
        $model = explode("\n", $model);
        foreach ($model as $index => $line) {
            // Remove non-informative lines
            if (Str::startsWith($line, $needles)) {
                unset($model[$index]);
                continue;
            }

            $line = rtrim($line);

            $model[$index] = $line;
        }
        $model = implode("\n", $model);

        // Remove multiple newlines
        $model = preg_replace('/\n{3,}/', "\n\n", $model);

        file_put_contents(OUTPUT_PATH.'/model-pruned.txt', rtrim($model)."\n");
    }
}
