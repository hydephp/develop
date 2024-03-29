<?php

declare(strict_types=1);

/**
 * @internal
 *
 * @usage php monorepo/scripts/FileFormatter.php [--git]
 */
require_once __DIR__.'/../../vendor/autoload.php';

$timeStart = microtime(true);

$filesChanged = 0;
$linesCounted = 0;
$dryRun = true;

enum Settings: string
{
    case UseUnixFileEndings = 'useUnixFileEndings';
    case ReplaceTabsWithSpaces = 'replaceTabsWithSpaces';
    case TrimTrailingSpaces = 'trimTrailingSpaces';
    case TrimMultipleEmptyLines = 'trimMultipleEmptyLines';
    case ForceEmptyLinesAtEndOfFile = 'forceEmptyLinesAtEndOfFile';
}

$settings = [
    Settings::UseUnixFileEndings,
    Settings::ReplaceTabsWithSpaces,
    Settings::TrimTrailingSpaces,
    // Settings::TrimMultipleEmptyLines,
    Settings::ForceEmptyLinesAtEndOfFile,
];

class CodeFormatter
{
    protected string $input;
    protected string $output;
    protected string $filename;
    protected array $settings;

    public function __construct(string $input, string $filename, array $settings)
    {
        $this->input = $input;
        $this->filename = $filename;
        $this->settings = $settings;

        $this->run();
    }

    protected function run(): void
    {
        $text = $this->input;
        $filename = $this->filename;

        $text = $this->useUnixFileEndings($text);
        $text = $this->replaceTabsWithSpaces($text);

        if (empty(trim($text))) {
            // Warn
            global $warnings;
            $warnings[] = "File $filename is empty";

            return;
        }

        $lines = explode("\n", $text);
        $new_lines = [];

        $last_line = '';

        foreach ($lines as $index => $line) {
            global $linesCounted;
            $linesCounted++;

            /** Normalization */

            // Remove global function imports from tests
            if (str_ends_with($filename, 'Test.php')) {
                if (str_starts_with($line, 'use function') && ! str_contains($line, '\\')) {
                    continue;
                }
            }

            // Remove multiple empty lines
            if (in_array(Settings::TrimMultipleEmptyLines, $this->settings)) {
                if (trim($line) == '' && trim($last_line) == '') {
                    continue;
                }
            }

            $line = $this->trimTrailingSpaces($line);

            $new_lines[] = $line;
            $last_line = $line;
        }

        $new_content = implode("\n", $new_lines);
        if (in_array(Settings::ForceEmptyLinesAtEndOfFile, $this->settings)) {
            $new_content = trim($new_content);
            $shouldEndWithNewLine = ! str_ends_with($filename, '.blade.php');
            if ($shouldEndWithNewLine) {
                $new_content .= "\n";
            }
        }
        $this->output = $new_content;
    }

    public function getOutput(): string
    {
        return $this->output;
    }

    protected function useUnixFileEndings(string $text): string
    {
        if (! in_array(Settings::UseUnixFileEndings, $this->settings)) {
            return $text;
        }

        return str_replace("\r\n", "\n", $text);
    }

    protected function replaceTabsWithSpaces(string $text): string
    {
        if (! in_array(Settings::ReplaceTabsWithSpaces, $this->settings)) {
            return $text;
        }

        return str_replace("\t", '    ', $text);
    }

    protected function trimTrailingSpaces(string $line): string
    {
        if (! in_array(Settings::TrimTrailingSpaces, $this->settings)) {
            return $line;
        }

        $line = rtrim($line);

        return $line;
    }
}

function format_file($filename): void
{
    // echo 'Handling '.$filename."\n";
    $stream = file_get_contents($filename);

    global $settings;
    $formatter = new CodeFormatter($stream, $filename, $settings);
    $new_content = $formatter->getOutput();

    global $dryRun;
    if (! $dryRun) {
        file_put_contents($filename, $new_content);
    }

    if ($new_content !== $stream) {
        echo 'Saving '.$filename."\n";
        if ($dryRun) {
            echo "\33[37m";
            echo linediff($stream, $new_content);
            echo "\33[0m";
        }
        global $filesChanged;
        $filesChanged++;
    }
}

function linediff(string $a, string $b): string
{
    $a = explode("\n", $a);
    $b = explode("\n", $b);

    $diffed = array_diff($a, $b);
    $wasLastLineEmpty = false;
    $diff = '';
    foreach ($diffed as $line) {
        if (trim($line) == '') {
            if ($wasLastLineEmpty) {
                continue;
            }
            $wasLastLineEmpty = true;
        } else {
            $wasLastLineEmpty = false;
        }
        $diff .= "\u{0394}".$line."\n";
    }
    if (trim($diff) === "\u{0394}") {
        return 'Added newline at end of file'."\n";
    }

    return $diff;
}

function find_files(): array
{
    $files = [];

    $directories = [
        __DIR__.'/../../packages',
        __DIR__.'/../../tests',
        __DIR__.'/../../.github',
    ];

    foreach ($directories as $directory) {
        $files = array_merge($files, find_files_in_directory($directory));
    }

    return $files;
}

function find_files_in_directory(string $directory): array
{
    $files = [];
    $excludedDirectories = [
        'node_modules',
        'vendor',
    ];

    $extensions = [
        'php',
        'blade.php',
        'js',
        'css',
        'yml',
        'json',
        'md',
    ];

    $directory = realpath($directory);
    if ($directory === false) {
        return $files;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST,
        RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
    );

    foreach ($iterator as $file) {
        if ($file->isDir()) {
            continue;
        }

        // Skip excluded directories
        $path = $file->getPath();
        foreach ($excludedDirectories as $excludedDirectory) {
            if (str_contains($path, $excludedDirectory)) {
                continue 2;
            }
        }

        if (in_array($file->getExtension(), $extensions)) {
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

$codeFiles = find_files();

foreach ($codeFiles as $file) {
    format_file($file);
}

$time = round((microtime(true) - $timeStart) * 1000, 2);
$linesTransformed = number_format($linesCounted);
$fileCount = count($codeFiles);

echo "\n\n\033[32mAll done!\033[0m Formatted, normalized, and validated $linesTransformed lines of code in $fileCount files in {$time}ms\n";

if ($filesChanged > 0) {
    echo "\n\033[32m$filesChanged files were changed.\033[0m ";
} else {
    echo "\n\033[32mNo files were changed.\033[0m ";
}

// If --git flag is passed, make a git commit
if (isset($argv[1]) && $argv[1] === '--git') {
    if ($filesChanged > 0) {
        echo "\n\033[33mCommitting changes to git...\033[0m\n";
        passthru('git commit -am "Format Code"');
    } else {
        echo "\n\033[33mNo changes to commit\033[0m\n";
    }
}
