<?php

declare(strict_types=1);

/**
 * @internal
 */
require_once __DIR__.'/../../../vendor/autoload.php';

$timeStart = microtime(true);

$linesCounted = 0;

$links = [];

$warnings = [];

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
    $is_inside_fenced_fenced_code_block = false;
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

        // Check if line is a heading
        if (str_starts_with($line, '##')) {
            $was_last_line_heading = true;
        } else {
            $was_last_line_heading = false;
        }

        // Make sure there is a space before opening a fenced code block (search for ```language)
        if (str_starts_with($line, '```') && $line !== '```' && trim($last_line) != '') {
            if (! $is_inside_fenced_fenced_code_block) {
                $new_lines[] = '';
            }
        }

        // Check if line is a escaped fenced code block
        if (str_starts_with($line, '````')) {
            $is_inside_fenced_fenced_code_block = ! $is_inside_fenced_fenced_code_block;
        }

        // Remove trailing spaces
        $line = rtrim($line);

        $new_lines[] = $line;
        $last_line = $line;

        /** Linting */

        // Add any links to buffer, so we can check them later
        preg_match_all('/\[([^\[]+)]\((.*)\)/', $line, $matches);
        if (count($matches) > 0) {
            foreach ($matches[2] as $match) {
                // If link is for an anchor, prefix the filename
                if (str_starts_with($match, '#')) {
                    $match = basename($filename).$match;
                }

                global $links;
                $links[] = [
                    'filename' => $filename,
                    'line' => $index + 1,
                    'link' => $match,
                ];
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
                $warnings[] = sprintf('Legacy marker found in %s:%s Found "%s"', $filename, $index + 1, $marker);
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

if (count($warnings) > 0) {
    echo "\n\033[31mWarnings:\033[0m \033[33m".count($warnings)." found \033[0m \n";
    foreach ($warnings as $warning) {
        echo " - $warning\n";
    }
}

// Just to make PhpStorm happy
$links[] = [
    'filename' => '',
    'line' => 1,
    'link' => '',
];

if (count($links) > 0) {
    foreach ($links as $data) {
        $link = $data['link'];
        $filename = $data['filename'];
        $line = $data['line'];

        if (str_starts_with($link, 'http')) {
            continue;
        }

        if (str_starts_with($link, '#')) {
            continue;
        }
    }
}

$time = round((microtime(true) - $timeStart) * 1000, 2);
$linesTransformed = number_format($linesCounted);
$fileCount = count($markdownFiles);

echo "\n\n\033[32mAll done!\033[0m Formatted, normalized, and validated $linesTransformed lines of Markdown in $fileCount files in {$time}ms\n";
