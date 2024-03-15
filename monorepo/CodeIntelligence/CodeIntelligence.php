<?php

/**
 * @internal This script is part of the internal monorepo tools.
 *
 * @usage php monorepo/CodeIntelligence/CodeIntelligence.php
 * @usage php -S localhost:8000 monorepo/CodeIntelligence/CodeIntelligence.php
 */

declare(strict_types=1);

use Illuminate\Support\Str;
use Hyde\Foundation\HydeKernel;
use Hyde\Markdown\Models\MarkdownDocument;

if (php_sapi_name() !== 'cli') {
    // Run the file and proxy the dashboard page for a live browser preview
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
    $this->info('Generating code and documentation intelligence...');
    $this->line();

    $generator = new CodeIntelligence();

    // Documentation analysis
    task('discover pages', fn () => $generator->discoverPages());
    task('assemble model', fn () => $generator->assembleModel());
    task('create pruned model', fn () => $generator->createPrunedModel());
    task('generate model data', fn () => $generator->getModelStatistics());

    // Dashboard generation
    task('create dashboard page', fn () => $generator->createDashboardPage());

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
        ...array_values($generator->getModelStatistics())
    ));

    $this->line();
    $this->line('Dashboard page generated at '.OUTPUT_PATH.'/dashboard.html');

    $this->line();
    $this->info(sprintf('Time taken: %s. Memory used: %s',
        number_format((microtime(true) - TIME_START) * 1000, 2).'ms',
        number_format(memory_get_peak_usage() / 1024 / 1024, 2).'MB'
    ));

    return 0;
});

class CodeIntelligence
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
        $data = $this->getDashboardData();
        $dashboard = file_get_contents(__DIR__.'/dashboard-template.php');

        foreach ($data as $key => $value) {
            $dashboard = str_replace('<?php echo $'.$key.'; ?>', $value, $dashboard);
        }

        file_put_contents(OUTPUT_PATH.'/dashboard.html', $dashboard);
    }

    /** @return array<string, mixed> */
    protected function getDashboardData(): array
    {
        return [
            'modelStatistics' => $this->makeModelStatisticsTable(),
            'modelSections' => $this->makeModelSections(),
            'headingsTable' => $this->makeHeadingsTable(),
            'headingsCount' => number_format(substr_count($this->makeHeadingsTable(), '<tr>') - 1),
            'modelRaw' => e(file_get_contents(OUTPUT_PATH.'/model.txt')),
        ];
    }

    protected function makeModelStatisticsTable(): string
    {
        $data = $this->getModelStatistics();

        $table = ['<tr><th>Model</th><th>Size</th><th>Words</th><th>Lines</th></tr>'];

        $table[] = sprintf('<tr><th>Full</th><td>%s</td><td>%s</td><td>%s</td></tr>',
            $data['Model size'],
            $data['Model words'],
            $data['Model lines']
        );

        $table[] = sprintf('<tr><th>Pruned</th><td>%s</td><td>%s</td><td>%s</td></tr>',
            $data['Pruned model size'],
            $data['Pruned model words'],
            $data['Pruned model lines']
        );

        $extraData = [
            'Compression' => $data['Pruned model compression'],
            'Reading time' => \Hyde\Support\ReadingTime::fromFile(OUTPUT_PATH.'/model-pruned.txt')->getFormatted('%d mins'),
        ];

        foreach ($extraData as $key => $value) {
            $table[] = sprintf('<tr><th>%s</th><td colspan="3" align="right">%s</td></tr>', $key, $value);
        }

        return implode("\n".str_repeat(' ', 20), $table);
    }

    protected function makeModelSections(): string
    {
        // Create textarea for each section
        $sections = explode('--- ', file_get_contents(OUTPUT_PATH.'/model.txt'));

        // Skip the first empty section
        array_shift($sections);

        $html = '';
        foreach ($sections as $section) {
            // Extract title from first line
            $section = explode("\n", $section, 2);
            $title = rtrim(array_shift($section), '- ');
            $section = implode("\n", $section);

            $html .= '<h4 class="mt-2">'.e($title).'</h4>';
            $html .= '<textarea rows="10" cols="80" style="width: 100%; white-space: pre; font-family: monospace;">'.e($section).'</textarea>';
        }

        return $html;
    }

    protected function makeHeadingsTable(): string
    {
        $headings = [];

        $model = file(OUTPUT_PATH.'/model.txt');

        $isInCodeBlock = false;

        foreach ($model as $line) {
            if (Str::startsWith($line, '```')) {
                $isInCodeBlock = ! $isInCodeBlock;
            }

            if ($isInCodeBlock) {
                continue;
            }

            if (Str::startsWith($line, '#')) {
                $headings[] = trim($line);
            }
        }

        $rows = [];

        foreach ($headings as $heading) {
            $headingText = trim($heading, '# ');
            $isTitleCase = $headingText === \Hyde\Hyde::makeTitle($headingText);

            $rows[] = [
                'level' => substr_count($heading, '#'),
                'text' => $headingText,
                'case' => $isTitleCase ? 'Title' : 'Sentence',
            ];
        }

        usort($rows, fn ($a, $b) => $a['level'] <=> $b['level']);

        $html = '<tr><th>Level</th><th>Heading</th><th>Case type</th></tr>'."\n";

        foreach ($rows as $row) {
            $html .= '<tr><td>'.implode('</td><td>', $row).'</td></tr>'."\n";
        }

        return $html;
    }

    public function getModelStatistics(): array
    {
        return $this->statistics ??= [
            'Model size' => number_format(filesize(OUTPUT_PATH.'/model.txt') / 1024, 2).' KB',
            'Model words' => number_format(str_word_count(file_get_contents(OUTPUT_PATH.'/model.txt'))),
            'Model lines' => number_format(count(file(OUTPUT_PATH.'/model.txt')) + 1),

            'Pruned model size' => number_format(filesize(OUTPUT_PATH.'/model-pruned.txt') / 1024, 2).' KB',
            'Pruned model words' => number_format(str_word_count(file_get_contents(OUTPUT_PATH.'/model-pruned.txt'))),
            'Pruned model lines' => number_format(count(file(OUTPUT_PATH.'/model-pruned.txt')) + 1),
            'Pruned model compression' => number_format((1 - (filesize(OUTPUT_PATH.'/model-pruned.txt') / filesize(OUTPUT_PATH.'/model.txt'))) * 100, 2).'%',
        ];
    }
}
