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

    // Markup analysis
    task('generate markup analysis', fn () => $generator->generateMarkupAnalysis());

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

    /** @var array<string, string> */
    protected array $markupStatistics;

    /** @var array<string, string> */
    protected array $bladeFiles;

    /** @var array<string, int> */
    protected array $bladeElementIdentifiers;
    /** @var array<string, int> */
    protected array $bladeElementClasses;

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
            'markupStatistics' => $this->formatMarkupStatistics(),
            'bladeFiles' => $this->formatBladeFileList(),
            'bladeElementIdentifiers' => $this->formatBladeElementIdentifiers(),
            'bladeElementClasses' => $this->formatBladeElementClasses(),
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

    public function generateMarkupAnalysis(): void
    {
        $this->bladeFiles = $this->findBladeFiles();
        $this->bladeElementIdentifiers = $this->findBladeElementIdentifiers();
        $this->bladeElementClasses = $this->findBladeElementClasses();

        $this->markupStatistics = [
            'bladeFileCount' => number_format(count($this->bladeFiles)),
            'bladeFileLines' => number_format(array_sum(array_map(fn (string $file): int => substr_count($file, "\n"), $this->bladeFiles))),
            'bladeFileSize' => number_format(array_sum(array_map('strlen', $this->bladeFiles)) / 1024, 2).' KB',
        ];
    }

    protected function formatMarkupStatistics(): string
    {
        return implode("\n", array_map(function (string $key, string $value): string {
            return sprintf('<tr><td>%s:</td> <td class="text-end">%s</td></tr>', $this->kernel->makeTitle($key), $value);
        }, array_keys($this->markupStatistics), $this->markupStatistics));
    }

    protected function formatBladeFileList(): string
    {
        $html = '';

        foreach ($this->bladeFiles as $file => $contents) {
            $html .= '<details><summary>'.e($file).'</summary><textarea rows="10" cols="80" style="width: 100%; white-space: pre; font-family: monospace;">'.e($contents).'</textarea></details>';
        }

        return $html;
    }

    protected function formatBladeElementIdentifiers(): string
    {
        $html = '<ul>';

        foreach ($this->bladeElementIdentifiers as $identifier => $count) {
            $occurrence = $count === 1 ? 'occurrence' : 'occurrences';
            $isKebabCase = Str::contains($identifier, '-') || (Str::kebab($identifier) === $identifier);

            if (! str_contains($identifier, '$')) {
                $identifier = '#'.$identifier;
            }

            $errorString = $isKebabCase ? '' : '<span title="This identifier is not in kebab-case">⚠️</span>';

            $html .= sprintf("<li><code>%s</code> <small class=\"text-muted\">(%d %s)</small>%s</li>\n", e($identifier), $count, $occurrence, $errorString);
        }

        return $html.'</ul>';
    }

    protected function formatBladeElementClasses(): string
    {
        $html = '<ul>';

        foreach ($this->bladeElementClasses as $class => $count) {
            $isKebabCase = Str::contains($class, '-') || (Str::kebab($class) === $class);
            $hasInlineBladeDirectives = Str::startsWith($class, '@');

            if (! $isKebabCase) {
                $errorString = '<span title="This class is not in kebab-case">⚠️</span>';
            } elseif ($hasInlineBladeDirectives) {
                $errorString = '<span title="This class contains Blade directives and could be improved by refactoring to a `@class()` syntax">⚠️</span>';
            } else {
                $errorString = '';
            }

            $occurrence = $count === 1 ? 'occurrence' : 'occurrences';

            $html .= sprintf("<li><code>%s</code> <small class=\"text-muted\">(%d %s)</small>%s</li>\n", e($class), $count, $occurrence, $errorString);
        }

        return $html.'</ul>';
    }

    /** @return array<string> */
    protected function findBladeFiles(): array
    {
        $sourceDirs = [
            'packages/framework/resources/views',
        ];

        $files = [];

        foreach ($sourceDirs as $sourceDir) {
            $directory = new RecursiveDirectoryIterator(BASE_PATH.'/'.$sourceDir);
            $iterator = new RecursiveIteratorIterator($directory);
            $regex = new RegexIterator($iterator, '/^.+\.blade\.php$/i', RecursiveRegexIterator::GET_MATCH);

            foreach ($regex as $file) {
                $files[substr($file[0], strlen(BASE_PATH) + 1)] = file_get_contents($file[0]);
            }
        }

        return $files;
    }

    /** @return array<string, int> */
    protected function findBladeElementIdentifiers(): array
    {
        // Create a list of all element identifiers in the Blade files, along with the count of how many times they appear
        $identifiers = [];

        foreach ($this->bladeFiles as $contents) {
            $matches = [];
            preg_match_all('/id="([^"]+)"/', $contents, $matches);
            foreach ($matches[1] as $match) {
                $identifiers[$match] = ($identifiers[$match] ?? 0) + 1;
            }
        }

        // Sort alphabetically
        ksort($identifiers);

        return $identifiers;
    }

    /** @return array<string, int> */
    protected function findBladeElementClasses(): array
    {
        // Create a list of all element classes in the Blade files, along with the count of how many times they appear
        $classes = [];

        foreach ($this->bladeFiles as $contents) {
            $matches = [];
            preg_match_all('/class="([^"]+)"/', $contents, $matches);
            foreach ($matches[1] as $match) {
                $match = explode(' ', $match);
                foreach ($match as $class) {
                    $class = trim($class, '?:\'{}');

                    if (blank($class)) {
                        continue;
                    }

                    // Don't add Tailwind classes
                    $tailwindClasses = [
                        '-left',
                        '-mb',
                        '-ml',
                        '-mr',
                        '-mt',
                        '-mx',
                        '-my',
                        '2xl',
                        '3xl',
                        '4xl',
                        '5xl',
                        '6xl',
                        '7xl',
                        '8xl',
                        'absolute',
                        'align',
                        'antialiased',
                        'appearance-none',
                        'appearance',
                        'auto-cols',
                        'auto-rows',
                        'bg',
                        'block',
                        'border-b',
                        'border-collapse',
                        'border-dashed',
                        'border-dotted',
                        'border-double',
                        'border-l',
                        'border-none',
                        'border-r',
                        'border-separate',
                        'border-solid',
                        'border-t',
                        'border-x',
                        'border-y',
                        'border',
                        'bottom',
                        'capitalize',
                        'clear',
                        'col-end',
                        'col-start',
                        'col',
                        'container',
                        'cursor-auto',
                        'cursor',
                        'drop',
                        'duration',
                        'fill',
                        'fixed',
                        'fixed',
                        'flex-grow',
                        'flex-shrink',
                        'flex',
                        'float',
                        'focus',
                        'font',
                        'fr',
                        'gap',
                        'grid-cols',
                        'grid-rows',
                        'grid',
                        'group',
                        'h',
                        'hidden',
                        'hover',
                        'inline',
                        'inset',
                        'inset',
                        'invisible',
                        'invisible',
                        'justify',
                        'leading',
                        'left',
                        'lg',
                        'lowercase',
                        'm',
                        'max-h',
                        'max-w',
                        'mb',
                        'md',
                        'min-h',
                        'min-w',
                        'ml',
                        'mr',
                        'mt',
                        'mx',
                        'my',
                        'not-prose',
                        'object',
                        'opacity',
                        'order',
                        'origin',
                        'overflow',
                        'overflow',
                        'overscroll',
                        'p',
                        'pb',
                        'pl',
                        'place',
                        'pr',
                        'prose',
                        'pt',
                        'px',
                        'py',
                        'relative',
                        'right',
                        'rotate',
                        'rounded-b',
                        'rounded-bl',
                        'rounded-br',
                        'rounded-l',
                        'rounded-r',
                        'rounded-t',
                        'rounded-tl',
                        'rounded-tr',
                        'rounded',
                        'row-end',
                        'row-start',
                        'row',
                        'scale',
                        'scroll',
                        'select',
                        'shadow',
                        'skew',
                        'sm',
                        'space',
                        'sr',
                        'static',
                        'sticky',
                        'table-auto',
                        'table-fixed',
                        'text',
                        'top',
                        'tracking',
                        'transform',
                        'transition',
                        'translate',
                        'uppercase',
                        'visible',
                        'visible',
                        'w',
                        'whitespace',
                        'xl',
                        'xs',
                        'z',
                        'z',
                        'dark',
                    ];
                    foreach ($tailwindClasses as $twClass) {
                        if (($class === $twClass) || str_starts_with($class, $twClass.'-') || str_starts_with($class, $twClass.':')) {
                            continue 2; // Found a Tailwind class, skip it
                        }
                    }

                    $classes[$class] = ($classes[$class] ?? 0) + 1;
                }
            }
        }

        // Sort alphabetically
        ksort($classes);

        return $classes;
    }
}
