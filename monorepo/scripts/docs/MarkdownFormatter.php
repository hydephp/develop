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

// Buffer headings so we can check for style
$headings = []; // [filename => [line => heading]]
$checksHeadings = false;

class MarkdownFormatter
{
    protected string $input;
    protected string $output;

    public function __construct(string $input)
    {
        $this->input = $input;
    }

    protected function run(): void
    {
        $this->output = $this->input;
    }
}

function find_markdown_files($dir): array
{
    $markdown_files = [];

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        // Skip _data directory
        if (str_contains($file->getPathname(), '_data')) {
            continue;
        }

        if ($file->isFile() && strtolower($file->getExtension()) == 'md') {
            $markdown_files[] = realpath($file->getPathname());
        }
    }

    return $markdown_files;
}

function handle_file(string $file): void
{
    echo 'Handling '.$file."\n";

    normalize_lines($file);
}

function normalize_lines($filename): void
{
    $stream = file_get_contents($filename);

    $text = $stream;
    $text = str_replace("\r\n", "\n", $text);
    $text = str_replace("\t", '    ', $text);

    if (empty(trim($text))) {
        // Warn
        global $warnings;
        $warnings[] = "File $filename is empty";

        return;
    }

    $lines = explode("\n", $text);
    $new_lines = [];

    $last_line = '';
    $was_last_line_heading = false;
    $is_inside_fenced_code_block = false;
    $is_inside_fenced_fenced_code_block = false;
    $firstHeadingLevel = null;
    foreach ($lines as $index => $line) {
        global $linesCounted;
        $linesCounted++;

        /** Normalization */

        // Remove multiple empty lines
        if (trim($line) == '' && trim($last_line) == '') {
            continue;
        }

        // Make sure there is a space after headings
        if ($was_last_line_heading && trim($line) != '') {
            $new_lines[] = '';
        }

        // Make sure there are two empty lines before level 2 headings (but not if it's the first l2 heading)
        if ($is_inside_fenced_code_block !== true && str_starts_with($line, '## ') && $index > $firstHeadingLevel + 3) {
            $new_lines[] = '';
        }

        if ($firstHeadingLevel === null && str_starts_with($line, '# ')) {
            $firstHeadingLevel = $index;
        }

        // Check if line is a heading
        if (str_starts_with($line, '##')) {
            $was_last_line_heading = true;
            global $headings;
            $headings[$filename][$index + 1] = $line;
        } else {
            $was_last_line_heading = false;
        }

        // Make sure there is a space before opening a fenced code block (search for ```language)
        if (str_starts_with($line, '```') && $line !== '```' && trim($last_line) != '') {
            if (! $is_inside_fenced_fenced_code_block) {
                $new_lines[] = '';
            }
        }

        // Check if line is a  fenced code block
        if (str_starts_with($line, '``')) {
            $is_inside_fenced_code_block = ! $is_inside_fenced_code_block;
        }

        // Check if line is an escaped fenced code block
        if (str_starts_with($line, '````')) {
            $is_inside_fenced_fenced_code_block = ! $is_inside_fenced_fenced_code_block;
        }

        // Remove trailing spaces
        $line = rtrim($line);

        $new_lines[] = $line;
        $last_line = $line;

        /** Linting */

        // if not inside fenced code block
        if (! $is_inside_fenced_code_block) {
            // Add any links to buffer, so we can check them later
            preg_match_all('/\[([^\[]+)]\((.*)\)/', $line, $matches);
            if (count($matches) > 0) {
                foreach ($matches[2] as $match) {
                    // If link is for an anchor, prefix the filename
                    if (str_starts_with($match, '#')) {
                        $match = 'ANCHOR_'.basename($filename).$match;
                    }

                    global $links;
                    $links[] = [
                        'filename' => $filename,
                        'line' => $index + 1,
                        'link' => $match,
                    ];
                }
            }

            // Check for un-backtick-ed inline code
            // If line contains $
            if (str_contains($line, '$') && ! str_contains($line, '[Blade]:') && ! str_contains($line, '$ php')) {
                // Check character before the $ is not a backtick
                $pos = strpos($line, '$');
                if ($pos > 0) {
                    $charBefore = substr($line, $pos - 1, 1);
                    if ($charBefore !== '`') {
                        global $warnings;
                        $warnings['Inline code'][] = sprintf('Unformatted inline code found in %s:%s', $filename, $index + 1);
                    }
                }
            }
            // If line contains command
            if (str_contains($line, 'php hyde') && ! str_contains($line, '[Blade]:') && ! str_contains($line, '$ php')) {
                // Check character before the php hyde is not a backtick
                $pos = strpos($line, 'php hyde');
                if ($pos > 0) {
                    $charBefore = substr($line, $pos - 1, 1);
                    if ($charBefore !== '`') {
                        global $warnings;
                        $warnings['Inline code'][] = sprintf('Unformatted inline command found in %s:%s', $filename, $index + 1);
                    }
                }
            }
            // If word ends in .php
            if (str_contains($line, '.php') && ! str_contains($line, '[Blade]:') && ! str_contains($line, '$ php') && ! str_contains($line, 'http') && ! str_contains(strtolower($line), 'filepath')) {
                // Check character after the .php is not a backtick
                $pos = strpos($line, '.php');
                if ($pos > 0) {
                    $charAfter = substr($line, $pos + 4, 1);
                    if ($charAfter !== '`') {
                        global $warnings;
                        $warnings['Inline code'][] = sprintf('Unformatted inline filename found in %s:%s', $filename, $index + 1);
                    }
                }
            }

            // If word ends in .json
            if (str_contains($line, '.json') && ! str_contains($line, '[Blade]:') && ! str_contains($line, '$ json') && ! str_contains($line, 'http') && ! str_contains(strtolower($line), 'filepath')) {
                // Check character after the .json is not a backtick
                $pos = strpos($line, '.json');
                if ($pos > 0) {
                    $charAfter = substr($line, $pos + 5, 1);
                    if ($charAfter !== '`') {
                        global $warnings;
                        $warnings['Inline code'][] = sprintf('Unformatted inline filename found in %s:%s', $filename, $index + 1);
                    }
                }
            }
            // if word ends with ()
            if (str_contains($line, '()') && ! str_contains($line, '[Blade]:')) {
                // Check character after the () is not a backtick
                $pos = strpos($line, '()');
                if ($pos > 0) {
                    $charAfter = substr($line, $pos + 2, 1);
                    if ($charAfter !== '`') {
                        global $warnings;
                        $warnings['Inline code'][] = sprintf('Unformatted inline function found in %s:%s', $filename, $index + 1);
                    }
                }
            }

            // Check for invalid command signatures
            if (str_contains($line, 'php hyde')) {
                // Extract signature from line
                $start = strpos($line, 'php hyde');
                $substr = substr($line, $start);
                $explode = explode(' ', $substr, 3);
                $signature = $explode[0].' '.$explode[1].' '.$explode[2];
                $end = strpos($signature, '`');
                if ($end === false) {
                    $end = strpos($signature, '<');
                    if ($end === false) {
                        $end = strlen($signature);
                    }
                }
                $signature = substr($signature, 0, $end);
                $signatures = getSignatures();
                if (! in_array($signature, $signatures)) {
                    global $warnings;
                    $warnings['Invalid command signatures'][] = sprintf('Invalid command signature \'%s\' found in %s:%s', $signature, $filename, $index + 1);
                }
            }
        }

        // Check if line is too long
        if (strlen($line) > 120) {
            global $warnings;
            // $warnings[] = 'Line '.$linesCounted.' in file '.$filename.' is too long';
        }

        // Warn if documentation contains legacy markers (experimental, beta, etc)
        $markers = ['experimental', 'beta', 'alpha', 'v0.'];
        foreach ($markers as $marker) {
            if (str_contains($line, $marker)) {
                global $warnings;
                $warnings['Legacy markers'][] = sprintf('Legacy marker found in %s:%s Found "%s"', $filename, $index + 1, $marker);
            }
        }

        // Warn when legacy terms are used (for example slug instead of identifier/route key)
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

    $new_content = implode("\n", $new_lines);
    $new_content = trim($new_content)."\n";
    file_put_contents($filename, $new_content);

    if ($new_content !== $stream) {
        global $filesChanged;
        $filesChanged++;
    }
}

$dir = __DIR__.'/../../../docs';
$markdownFiles = find_markdown_files($dir);

foreach ($markdownFiles as $file) {
    handle_file($file);
}

// Just to make PhpStorm happy
$links[] = [
    'filename' => '',
    'line' => 1,
    'link' => '',
];

if (count($links) > 0) {
    $uniqueLinks = [];

    foreach ($links as $data) {
        $link = $data['link'];
        $filename = $data['filename'];
        $line = $data['line'];

        if (str_starts_with($link, 'http')) {
            // Check for outdated links
            // laravel.com/docs/9.x
            if (str_contains($link, 'laravel.com/docs/9.x')) {
                $warnings['Outdated links'][] = "Outdated documentation link to $link found in $filename:$line";
            }
            continue;
        }

        if (str_starts_with($link, '#')) {
            continue;
        }

        // Remove hash for anchors
        $link = explode('#', $link)[0];
        // Remove anything before spaces (image alt text)
        $link = explode(' ', $link)[0];
        // Trim any non-alphanumeric characters from the end of the link
        $link = rtrim($link, '.,;:!?)');

        if (! str_starts_with($link, 'ANCHOR_')) {
            // Add to new unique array
            $uniqueLinks[$link] = "$filename:$line";
        }
    }

    $base = __DIR__.'/../../../docs';
    // find all directories in the docs folder
    $directories = array_filter(glob($base.'/*'), 'is_dir');

    foreach ($uniqueLinks as $link => $location) {
        // Check uses pretty urls
        if (str_ends_with($link, '.html')) {
            $warnings['Bad links'][] = "Link to $link in $location should not use .html extension";
            continue;
        }

        // Check does not end with .md
        if (str_ends_with($link, '.md')) {
            $warnings['Bad links'][] = "Link to $link in $location must not use .md extension";
            continue;
        }

        // Check if file exists
        if (! file_exists($base.'/'.$link)) {
            $hasMatch = false;
            foreach ($directories as $directory) {
                if (file_exists($directory.'/'.$link.'.md')) {
                    $hasMatch = true;
                    break;
                }
            }

            if (! $hasMatch) {
                // Check that link is not for search (dynamic page)
                if (! str_contains($link, 'search')) {
                    $warnings['Broken links'][] = "Broken link to $link found in $location";
                }
            }
        }
    }
}

function getSignatures(): array
{
    static $signatures = null;

    if ($signatures === null) {
        $cache = __DIR__.'/../cache/hyde-signatures.php';
        if (file_exists($cache)) {
            $signatures = include $cache;
        } else {
            $signatures = [
                // Adds any hidden commands we know exist
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

// Just to make PhpStorm happy
$headings['foo.md'][1] = '## Bar';

if ($checksHeadings && count($headings)) {
    foreach ($headings as $filename => $fileHeadings) {
        $headingLevels = [];
        foreach ($fileHeadings as $heading) {
            $headingLevel = substr_count($heading, '#');
            $headingLevels[] = $headingLevel;

            // Check for style: 1-2 headings should be title case, 3+ should be sentence case
            $headingText = trim(str_replace('#', '', $heading));
            $titleCase = Hyde\make_title($headingText);
            $alwaysUppercase = ['PHP', 'HTML', 'CLI'];
            $alwaysLowercase = ['to'];
            $titleCase = str_ireplace($alwaysUppercase, $alwaysUppercase, $titleCase);
            $titleCase = str_ireplace($alwaysLowercase, $alwaysLowercase, $titleCase);

            $isTitleCase = $headingText === $titleCase;
            $sentenceCase = Str::ucfirst($headingText);
            $isSentenceCase = $headingText === $sentenceCase;
            $something = false;
            if ($headingLevel < 3) {
                if (! $isTitleCase) {
                    $warnings['Headings'][] = "Heading '$headingText' should be title case in $filename (expected '$titleCase')";
                }
            } else {
                if (! $isSentenceCase) {
                    $warnings['Headings'][] = "Heading '$headingText' should be sentence case in $filename (expected '$sentenceCase')";
                }
            }
        }
    }
}

if (count($warnings) > 0) {
    echo "\n\033[31mWarnings:\033[0m \033[33m".count($warnings, COUNT_RECURSIVE) - count($warnings)." found \033[0m \n";
    foreach ($warnings as $type => $messages) {
        echo "\n\033[33m$type:\033[0m \n";
        foreach ($messages as $message) {
            echo " - $message\n";
        }
    }
}

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
if ($warningCount > 0) {
    echo sprintf("\033[33m%s %s found.\033[0m", $warningCount, $warningCount === 1 ? 'warning' : 'warnings');
    if (file_exists(__DIR__.'/../cache/last-run-warnings-count.txt')) {
        $lastRunWarningsCount = (int) file_get_contents(__DIR__.'/../cache/last-run-warnings-count.txt');
        if ($warningCount < $lastRunWarningsCount) {
            echo sprintf(' Good job! You fixed %d %s!', $lastRunWarningsCount - $warningCount, $lastRunWarningsCount - $warningCount === 1 ? 'warning' : 'warnings');
        } elseif ($warningCount > $lastRunWarningsCount) {
            echo sprintf(' Uh oh! You introduced %d new %s!', $warningCount - $lastRunWarningsCount, $warningCount - $lastRunWarningsCount === 1 ? 'warning' : 'warnings');
        }
    }
}
file_put_contents(__DIR__.'/../cache/last-run-warnings-count.txt', $warningCount);
echo "\n";

// If --git flag is passed, make a git commit
if (isset($argv[1]) && $argv[1] === '--git') {
    if ($filesChanged > 0) {
        echo "\n\033[33mCommitting changes to git...\033[0m\n";
        passthru('git commit -am "Format Markdown"');
    } else {
        echo "\n\033[33mNo changes to commit\033[0m\n";
    }
}
