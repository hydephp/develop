<?php

declare(strict_types=1);

/**
 * @internal
 */

use Illuminate\Support\Str;

require_once __DIR__.'/../../../vendor/autoload.php';

$timeStart = microtime(true);

$filesChanged = 0;
$linesCounted = 0;
$links = [];
$warnings = [];

$headings = [];
$checksHeadings = true;
$fixesHeadings = true;

class MarkdownFormatter
{
    protected string $input;
    protected string $output;
    protected string $filename;

    public function __construct(string $input, string $filename = 'Input')
    {
        $this->input = $input;
        $this->filename = $filename;

        $this->run();
    }

    protected function run(): void
    {
        $text = $this->normalizeLineEndings($this->input);

        if ($this->isEmptyContent($text)) {
            return;
        }

        $lines = explode("\n", $text);
        $newLines = $this->processLines($lines);

        $this->output = $this->finalizeOutput($newLines);
    }

    protected function normalizeLineEndings(string $text): string
    {
        $text = str_replace("\r\n", "\n", $text);

        return str_replace("\t", '    ', $text);
    }

    protected function isEmptyContent(string $text): bool
    {
        if (empty(trim($text))) {
            global $warnings;
            $warnings[] = "File {$this->filename} is empty";

            return true;
        }

        return false;
    }

    /**
     * @param  string[]  $lines
     * @return string[]
     */
    protected function processLines(array $lines): array
    {
        $newLines = [];
        $lastLine = '';
        $wasLastLineHeading = false;
        $isInsideFencedCodeBlock = false;
        $isInsideFencedFencedCodeBlock = false;
        $firstHeadingLevel = null;

        foreach ($lines as $index => $line) {
            global $linesCounted;
            $linesCounted++;

            if ($this->shouldSkipLine($line, $lastLine)) {
                continue;
            }

            $line = $this->processLine($line, $lastLine, $wasLastLineHeading, $isInsideFencedCodeBlock, $isInsideFencedFencedCodeBlock, $firstHeadingLevel, $index);

            $newLines[] = $line;
            $lastLine = $line;
            $wasLastLineHeading = $this->isHeading($line);
            $isInsideFencedCodeBlock = $this->updateFencedCodeBlockStatus($line, $isInsideFencedCodeBlock);
            $isInsideFencedFencedCodeBlock = $this->updateFencedFencedCodeBlockStatus($line, $isInsideFencedFencedCodeBlock);
            $firstHeadingLevel = $this->updateFirstHeadingLevel($line, $firstHeadingLevel, $index);
        }

        return $newLines;
    }

    protected function shouldSkipLine(string $line, string $lastLine): bool
    {
        return trim($line) === '' && trim($lastLine) === '';
    }

    protected function processLine(string $line, string $lastLine, bool $wasLastLineHeading, bool $isInsideFencedCodeBlock, bool $isInsideFencedFencedCodeBlock, ?int $firstHeadingLevel, int $index): string
    {
        if ($wasLastLineHeading && trim($line) !== '') {
            $line = "\n".$line;
        }

        if (! $isInsideFencedCodeBlock && $this->isHeading($line) && trim($lastLine) !== '') {
            $line = "\n".$line;
        }

        if ($this->shouldAddEmptyLineBeforeFencedCodeBlock($line, $lastLine, $isInsideFencedFencedCodeBlock)) {
            $line = "\n".$line;
        }

        $this->processHeading($line, $index);

        return rtrim($line);
    }

    protected function isHeading(string $line): bool
    {
        return preg_match('/^#{1,6} /', $line) === 1;
    }

    protected function updateFencedCodeBlockStatus(string $line, bool $currentStatus): bool
    {
        return str_starts_with($line, '``') ? ! $currentStatus : $currentStatus;
    }

    protected function updateFencedFencedCodeBlockStatus(string $line, bool $currentStatus): bool
    {
        return str_starts_with($line, '````') ? ! $currentStatus : $currentStatus;
    }

    protected function updateFirstHeadingLevel(string $line, ?int $currentLevel, int $index): ?int
    {
        return $currentLevel === null && str_starts_with($line, '# ') ? $index : $currentLevel;
    }

    protected function shouldAddEmptyLineBeforeFencedCodeBlock(string $line, string $lastLine, bool $isInsideFencedFencedCodeBlock): bool
    {
        return str_starts_with($line, '```') && $line !== '```' && trim($lastLine) !== '' && ! $isInsideFencedFencedCodeBlock;
    }

    protected function processHeading(string $line, int $index): void
    {
        if ($this->isHeading($line)) {
            global $headings;
            $headings[$this->filename][$index + 1] = $line;
        }
    }

    protected function finalizeOutput(array $newLines): string
    {
        $newContent = implode("\n", $newLines);

        return trim($newContent)."\n";
    }

    public function getOutput(): string
    {
        return $this->output;
    }
}

function lint(string $filename): void
{
    $text = file_get_contents($filename);

    if (empty(trim($text))) {
        global $warnings;
        $warnings[] = "File $filename is empty";

        return;
    }

    $lines = explode("\n", $text);
    $isInsideFencedCodeBlock = false;

    foreach ($lines as $index => $line) {
        $isInsideFencedCodeBlock = processLine($filename, $line, $index, $isInsideFencedCodeBlock);
    }
}

function processLine(string $filename, string $line, int $index, bool $isInsideFencedCodeBlock): bool
{
    $isInsideFencedCodeBlock = updateFencedCodeBlockStatus($line, $isInsideFencedCodeBlock);

    if (! $isInsideFencedCodeBlock) {
        processLinks($filename, $line, $index);
        checkInlineCode($filename, $line, $index);
        checkCommandSignatures($filename, $line, $index);
        checkLineLengthAndLegacyMarkers($filename, $line, $index);
        checkLegacyTerms($filename, $line, $index);
    }

    return $isInsideFencedCodeBlock;
}

function updateFencedCodeBlockStatus(string $line, bool $currentStatus): bool
{
    return str_starts_with($line, '``') ? ! $currentStatus : $currentStatus;
}

function checkInlineCode(string $filename, string $line, int $index): void
{
    $inlineCodePatterns = [
        '/\$/' => 'Unformatted inline code',
        '/php hyde/' => 'Unformatted inline command',
        '/\.php/' => 'Unformatted inline filename',
        '/\.json/' => 'Unformatted inline filename',
        '/\(\)/' => 'Unformatted inline function',
    ];

    foreach ($inlineCodePatterns as $pattern => $warningType) {
        if (preg_match($pattern, $line) && ! isExcludedFromInlineCodeCheck($line)) {
            global $warnings;
            $warnings['Inline code'][] = sprintf('%s found in %s:%s', $warningType, $filename, $index + 1);
        }
    }
}

function isExcludedFromInlineCodeCheck(string $line): bool
{
    return str_contains($line, '[Blade]:') || str_contains($line, '$ php') || str_contains($line, 'http') || str_contains(strtolower($line), 'filepath');
}

function checkCommandSignatures(string $filename, string $line, int $index): void
{
    if (str_contains($line, 'php hyde')) {
        $signature = extractCommandSignature($line);
        $signatures = getSignatures();
        if (! in_array($signature, $signatures)) {
            global $warnings;
            $warnings['Invalid command signatures'][] = sprintf('Invalid command signature \'%s\' found in %s:%s', $signature, $filename, $index + 1);
        }
    }
}

function extractCommandSignature(string $line): string
{
    $start = strpos($line, 'php hyde');
    $substr = substr($line, $start);
    $explode = explode(' ', $substr, 3);
    $signature = $explode[0].' '.$explode[1].' '.$explode[2];
    $end = strpos($signature, '`') ?: strpos($signature, '<') ?: strlen($signature);

    return substr($signature, 0, $end);
}

function checkLineLengthAndLegacyMarkers(string $filename, string $line, int $index): void
{
    // Todo: Implement line length check
    $markers = ['experimental', 'beta', 'alpha', 'v0.'];
    foreach ($markers as $marker) {
        if (str_contains($line, $marker)) {
            global $warnings;
            $warnings['Legacy markers'][] = sprintf('Legacy marker found in %s:%s Found "%s"', $filename, $index + 1, $marker);
        }
    }
}

function checkLegacyTerms(string $filename, string $line, int $index): void
{
    $legacyTerms = [
        'slug' => '"identifier" or "route key"',
        'slugs' => '"identifiers" or "route keys"',
    ];

    foreach ($legacyTerms as $legacyTerm => $newTerm) {
        if (str_contains(strtolower($line), $legacyTerm)) {
            global $warnings;
            $warnings['Legacy terms'][] = sprintf('Legacy term found in %s:%s Found "%s", should be %s', $filename, $index + 1, $legacyTerm, $newTerm);
        }
    }
}

/**
 * @return string[]
 */
function find_markdown_files(string $dir): array
{
    $markdownFiles = [];

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if (str_contains($file->getPathname(), '_data')) {
            continue;
        }

        if ($file->isFile() && strtolower($file->getExtension()) === 'md') {
            $markdownFiles[] = realpath($file->getPathname());
        }
    }

    return $markdownFiles;
}

function handle_file(string $file): void
{
    echo "Handling $file\n";

    normalize_lines($file);
    lint($file);
}

function normalize_lines(string $filename): void
{
    $stream = file_get_contents($filename);

    $formatter = new MarkdownFormatter($stream, $filename);
    $newContent = $formatter->getOutput();

    file_put_contents($filename, $newContent);

    if ($newContent !== $stream) {
        global $filesChanged;
        $filesChanged++;
    }
}

/**
 * @return string[]
 */
function getSignatures(): array
{
    static $signatures = null;

    if ($signatures === null) {
        $cache = __DIR__.'/../cache/hyde-signatures.php';
        if (file_exists($cache)) {
            $signatures = include $cache;
        } else {
            $signatures = [
                'php hyde list',
                'php hyde change:sourceDirectory',
            ];
            $commandRaw = shell_exec('cd ../../../ && php hyde list --raw');
            foreach (explode("\n", $commandRaw) as $command) {
                $command = Str::before($command, ' ');
                $signatures[] = trim('php hyde '.$command);
            }
            file_put_contents($cache, '<?php return '.var_export($signatures, true).';');
        }
    }

    return $signatures;
}

function processHeadings(): void
{
    global $checksHeadings, $headings, $warnings, $fixesHeadings;

    if (! $checksHeadings || empty($headings)) {
        return;
    }

    \Hyde\Foundation\HydeKernel::setInstance(new \Hyde\Foundation\HydeKernel());

    foreach ($headings as $filename => $fileHeadings) {
        foreach ($fileHeadings as $heading) {
            $headingLevel = substr_count($heading, '#');
            $headingText = trim(str_replace('#', '', $heading));

            if (str_word_count($headingText) === 1 || str_word_count($headingText) > 5) {
                continue;
            }

            $expectedCase = $headingLevel < 3 ? Hyde\make_title($headingText) : Str::ucfirst($headingText);
            $expectedCase = adjustCaseForSpecialWords($expectedCase);

            if ($headingText !== $expectedCase) {
                $caseType = $headingLevel < 3 ? 'title' : 'sentence';
                $warnings['Headings'][] = "Heading '$headingText' should be $caseType case in $filename (expected '$expectedCase')";

                if ($fixesHeadings) {
                    fixHeading($filename, $heading, $headingLevel, $expectedCase);
                }
            }
        }
    }
}

function adjustCaseForSpecialWords(string $text): string
{
    $alwaysUppercase = ['PHP', 'HTML', 'CLI', 'API', 'YAML', 'XML', 'RSS', 'HydeKernel', 'GitHub'];
    $alwaysLowercase = ['to', 'it'];

    $text = str_ireplace($alwaysUppercase, $alwaysUppercase, $text);

    return str_ireplace($alwaysLowercase, $alwaysLowercase, $text);
}

function fixHeading(string $filename, string $heading, int $headingLevel, string $expectedCase): void
{
    $headingHashes = str_repeat('#', $headingLevel);
    $newHeading = "$headingHashes $expectedCase";

    $newContent = file_get_contents($filename);
    $newContent = str_replace($heading, $newHeading, $newContent);
    file_put_contents($filename, $newContent);

    echo "Fixed heading '$heading' to '$newHeading' in $filename\n";
}

function processLinks(): void
{
    global $links, $warnings;

    if (empty($links)) {
        return;
    }

    $uniqueLinks = [];

    foreach ($links as $data) {
        $link = $data['link'];
        $filename = $data['filename'];
        $line = $data['line'];

        if (str_starts_with($link, 'http')) {
            checkOutdatedLink($link, $filename, $line);
            continue;
        }

        if (str_starts_with($link, '#')) {
            continue;
        }

        $link = cleanLink($link);

        if (! str_starts_with($link, 'ANCHOR_')) {
            $uniqueLinks[$link] = "$filename:$line";
        }
    }

    validateLinks($uniqueLinks);
}

function checkOutdatedLink(string $link, string $filename, int $line): void
{
    global $warnings;

    if (str_contains($link, 'laravel.com/docs/9.x')) {
        $warnings['Outdated links'][] = "Outdated documentation link to $link found in $filename:$line";
    }
}

function cleanLink(string $link): string
{
    $link = explode('#', $link)[0];
    $link = explode(' ', $link)[0];

    return rtrim($link, '.,;:!?)');
}

function validateLinks(array $uniqueLinks): void
{
    global $warnings;

    $base = __DIR__.'/../../../docs';
    $directories = array_filter(glob($base.'/*'), 'is_dir');

    foreach ($uniqueLinks as $link => $location) {
        if (str_ends_with($link, '.html')) {
            $warnings['Bad links'][] = "Link to $link in $location should not use .html extension";
            continue;
        }

        if (str_ends_with($link, '.md')) {
            $warnings['Bad links'][] = "Link to $link in $location must not use .md extension";
            continue;
        }

        if (! file_exists($base.'/'.$link) && ! linkExistsInDirectories($link, $directories) && ! str_contains($link, 'search')) {
            $warnings['Broken links'][] = "Broken link to $link found in $location";
        }
    }
}

function linkExistsInDirectories(string $link, array $directories): bool
{
    foreach ($directories as $directory) {
        if (file_exists($directory.'/'.$link.'.md')) {
            return true;
        }
    }

    return false;
}

function displayWarnings(): void
{
    global $warnings;

    if (empty($warnings)) {
        return;
    }

    echo "\n\033[31mWarnings:\033[0m \033[33m".count($warnings, COUNT_RECURSIVE) - count($warnings)." found \033[0m \n";
    foreach ($warnings as $type => $messages) {
        echo "\n\033[33m$type:\033[0m \n";
        foreach ($messages as $message) {
            echo " - $message\n";
        }
    }
}

function displaySummary(): void
{
    global $timeStart, $linesCounted, $markdownFiles, $filesChanged, $warnings;

    $time = round((microtime(true) - $timeStart) * 1000, 2);
    $linesTransformed = number_format($linesCounted);
    $fileCount = count($markdownFiles);

    echo "\n\n\033[32mAll done!\033[0m Formatted, normalized, and validated $linesTransformed lines of Markdown in $fileCount files in {$time}ms\n";

    if ($filesChanged > 0) {
        echo "\n\033[32m$filesChanged files were changed.\033[0m ";
    } else {
        echo "\n\033[32mNo files were changed.\033[0m ";
    }

    $warningCount = count($warnings, COUNT_RECURSIVE) - count($warnings);
    displayWarningComparison($warningCount);
}

function displayWarningComparison(int $warningCount): void
{
    $lastRunWarningsFile = __DIR__.'/../cache/last-run-warnings-count.txt';

    if ($warningCount > 0) {
        echo sprintf("\033[33m%s %s found.\033[0m", $warningCount, $warningCount === 1 ? 'warning' : 'warnings');
        if (file_exists($lastRunWarningsFile)) {
            $lastRunWarningsCount = (int) file_get_contents($lastRunWarningsFile);
            if ($warningCount < $lastRunWarningsCount) {
                echo sprintf(' Good job! You fixed %d %s!', $lastRunWarningsCount - $warningCount, $lastRunWarningsCount - $warningCount === 1 ? 'warning' : 'warnings');
            } elseif ($warningCount > $lastRunWarningsCount) {
                echo sprintf(' Uh oh! You introduced %d new %s!', $warningCount - $lastRunWarningsCount, $warningCount - $lastRunWarningsCount === 1 ? 'warning' : 'warnings');
            }
        }
    }
    file_put_contents($lastRunWarningsFile, $warningCount);
    echo "\n";
}

function commitChanges(): void
{
    global $filesChanged;

    if (isset($argv[1]) && $argv[1] === '--git') {
        if ($filesChanged > 0) {
            echo "\n\033[33mCommitting changes to git...\033[0m\n";
            passthru('git commit -am "Format Markdown"');
        } else {
            echo "\n\033[33mNo changes to commit\033[0m\n";
        }
    }
}

// Main execution
$dir = __DIR__.'/../../../docs';
$markdownFiles = find_markdown_files($dir);

foreach ($markdownFiles as $file) {
    handle_file($file);
}

processHeadings();
processLinks();
displayWarnings();
displaySummary();
commitChanges();
