<?php

declare(strict_types=1);

/**
 * @internal
 */

use Illuminate\Support\Str;

require_once __DIR__.'/../../vendor/autoload.php';

$timeStart = microtime(true);

$filesChanged = 0;
$linesCounted = 0;
$dryRun = true;

class CodeFormatter
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
        $stream = $this->input;
        $filename = $this->filename;

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

        foreach ($lines as $index => $line) {
            global $linesCounted;
            $linesCounted++;

            /** Normalization */

            // Remove multiple empty lines
            if (trim($line) == '' && trim($last_line) == '') {
                continue;
            }

            // Remove trailing spaces
            $line = rtrim($line);

            $new_lines[] = $line;
            $last_line = $line;
        }

        $new_content = implode("\n", $new_lines);
        $new_content = trim($new_content)."\n";

        $this->output = $new_content;
    }

    public function getOutput(): string
    {
        return $this->output;
    }
}

function format_file($filename): void
{
    // echo 'Handling '.$filename."\n";
    $stream = file_get_contents($filename);

    $formatter = new CodeFormatter($stream, $filename);
    $new_content = $formatter->getOutput();

    global $dryRun;
    if (!$dryRun) {
        file_put_contents($filename, $new_content);
    }

    if ($new_content !== $stream) {
        echo 'Saving '.$filename."\n";
        global $filesChanged;
        $filesChanged++;
    }
}

function find_files(): array
{
    $files = [];

    $directories = [
        __DIR__.'/../../packages',
        __DIR__.'/../../tests',
    ];

    foreach ($directories as $directory) {
        $files = array_merge($files, find_files_in_directory($directory));
    }

    return $files;
}

function find_files_in_directory(string $directory): array
{
    $files = [];

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

        $filename = $file->getFilename();

        if (Str::endsWith($filename, '.php')) {
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
