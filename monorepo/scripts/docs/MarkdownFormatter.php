<?php

declare(strict_types=1);

/**
 * @internal
 */
$timeStart = microtime(true);

$linesCounted = 0;

$links = [];

require_once __DIR__.'/../../../vendor/autoload.php';

function find_markdown_files($dir): array
{
    $markdown_files = [];

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
    $text = str_replace("\t", '    ', $text);

    $lines = explode("\n", $text);
    $new_lines = [];

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
        if (str_starts_with($line, '##')) {
            $was_last_line_heading = true;
        } else {
            $was_last_line_heading = false;
        }

        // Remove trailing spaces
        $line = rtrim($line);

        $new_lines[] = $line;
        $last_line = $line;

        // Add any links to buffer so we can check them later
        preg_match_all('/\[([^\[]+)]\((.*)\)/', $line, $matches);
        if (count($matches) > 0) {
            foreach ($matches[2] as $match) {
                // If link is for an anchor, prefix the filename
                if (str_starts_with($match, '#')) {
                    $match = basename($filename).$match;
                }

                global $links;
                $links[] = $match;
            }
        }

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
