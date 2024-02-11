<?php

/**
 * @internal This script is part of the internal monorepo tools.
 */

declare(strict_types=1);

use Illuminate\Support\Str;
use Hyde\Foundation\HydeKernel;
use Hyde\Markdown\Models\MarkdownDocument;

if (php_sapi_name() !== 'cli') {
    // Run the file and proxy the dashboard page
    exec('php '.realpath(__FILE__).' 2>&1', $output, $returnCode);

    if ($returnCode !== 0) {
        echo '<pre>'.implode("\n", $output).'</pre>';
        exit(1);
    }

    return require_once __DIR__.'/results/dashboard.html';
}

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
    task('create dashboard page', fn () => $generator->createDashboardPage());

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
        $model = preg_replace('/<pre.*?<\/pre>/s', '', $model);

        $model = explode("\n", $model);

        // Normalizing pass
        $needles = ['<!-- ', '[//]: # ', '[Blade]: ', '--- redirects/', '<meta http-equiv="refresh" ', 'Redirecting you to ['];
        foreach ($model as $index => $line) {
            // Remove non-informative lines
            if (Str::startsWith($line, $needles)) {
                unset($model[$index]);
                continue;
            } elseif (Str::startsWith($line, '{') && Str::endsWith($line, '}')) {
                unset($model[$index]);
                continue;
            } elseif (Str::startsWith($line, '<div') && Str::endsWith($line, '>')) {
                unset($model[$index]);
                continue;
            } elseif (Str::startsWith($line, '<a name') && Str::endsWith($line, '></a>')) {
                unset($model[$index]);
                continue;
            } elseif (Str::startsWith($line, '|-') && Str::endsWith($line, '-|')) {
                // Table dividers
                unset($model[$index]);
                continue;
            } elseif (filled($line) && empty(str_replace(['|', ' '], '', $line))) {
                // Empty table row
                unset($model[$index]);
                continue;
            } elseif ($line === '</div>') {
                unset($model[$index]);
                continue;
            } elseif ($line === '---') {
                unset($model[$index]);
                continue;
            }

            // Remove multiple spaces
            $line = preg_replace('/\s+/', ' ', $line);

            // Remove heading tags
            $line = ltrim($line, '# ');

            // Trim colored and normal blockquotes (['>danger', '>info', '>success', '>warning'])
            $line = preg_replace('/^>\w+ /', '', $line);
            $line = ltrim($line, '> ');

            // Now we remove even more Markdown syntax to only keep the text

            // Replace links with their text (but put the text within quotes)
            $line = preg_replace('/\[(.*?)\]\(.*?\)/', "'\$1'", $line);

            // Remove bold and italic
            $line = preg_replace('/\*\*(.*?)\*\*/', '$1', $line);

            // Remove italic (underscore syntax where there are spaces around the underscore, as it otherwise removes snake_case words)
            $line = preg_replace('/\b_(.*?)_\b/', '$1', $line);

            // Remove HTML tags (This does remove some examples, like `<identifier>`)
            $line = strip_tags($line);

            // Replace tables with comma-separated values
            if (Str::startsWith($line, '|') && Str::endsWith($line, '|')) {
                $line = str_replace(['| |'], '|', $line);
                $line = trim(str_replace([' | '], ', ', $line), '| ');
                $line = str_replace('.,', '.', $line);
            }

            // Simplify lists with removed text
            if (Str::startsWith($line, '- \'') && Str::endsWith($line, '\'')) {
                // Remove the quotes
                $line = '- '.substr($line, 3, -1);
            }

            $model[$index] = rtrim($line);
        }

        // Uniqueness pass (we do this manually as array_unique removes empty lines)
        $uniqueLines = [];
        foreach ($model as $line) {
            if (empty($line) || ! in_array($line, $uniqueLines, true)) {
                $uniqueLines[] = $line;
            }
        }
        $model = $uniqueLines;

        $model = implode("\n", $model);

        // Remove multiple newlines
        $model = preg_replace('/\n{3,}/', "\n\n", $model);

        file_put_contents(OUTPUT_PATH.'/model-pruned.txt', rtrim($model)."\n");
    }

    public function createDashboardPage(): void
    {
        $dashboard = file_get_contents(__DIR__.'/dashboard-template.blade.php');

        $data = [];

        foreach ($data as $key => $value) {
            $dashboard = str_replace('{{ $'.$key.' }}', $value, $dashboard);
        }

        if (Str::contains($dashboard, '{{ $')) {
            throw new RuntimeException('Some variables were not replaced in the dashboard template');
        }

        file_put_contents(OUTPUT_PATH.'/dashboard.html', $dashboard);
    }
}
