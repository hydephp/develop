<?php

declare(strict_types=1);

$timeStart = microtime(true);

$linesCounted = 0;

require_once __DIR__.'/../../../vendor/autoload.php';

function find_markdown_files($dir): array
{
    $markdown_files = array();

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
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

    $lines = explode("\n", $text);
    $new_lines = array();

    $last_line = '';
    $was_last_line_heading = false;
    foreach ($lines as $line) {
        global $linesCounted;
        $linesCounted++;

        // Remove multiple empty lines
        if (trim($line) == '' && trim($last_line) == '') {
            continue;
        }

        // Make sure there is a space after headings
        if ($was_last_line_heading && trim($line) != '') {
            $new_lines[] = '';
        }

        // Check if line is a heading
        if (preg_match('/^#+/', $line)) {
            $was_last_line_heading = true;
        } else {
            $was_last_line_heading = false;
        }

        // Remove trailing spaces
        $line = rtrim($line);

        $new_lines[] = $line;
        $last_line = $line;
    }

    $new_content = implode("\n", $new_lines);
    $new_content = trim($new_content)."\n";
    file_put_contents($filename, $new_content);
}

$dir = __DIR__.'/../../../docs';
$markdownFiles = find_markdown_files($dir);

foreach ($markdownFiles as $file) {
    handle_file($file);
}

$timeEnd = microtime(true);
$time = $timeEnd - $timeStart;
$time *= 1000;
$time = round($time, 2);

$linesTransformed = number_format($linesCounted);

echo 'Transformed '.$linesTransformed.' lines of Markdown in '.$time.'ms';
