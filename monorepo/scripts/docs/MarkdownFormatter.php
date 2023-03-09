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
    $is_inside_fenced_code_block = false;
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


        // Check if line is a  fenced code block
        if (str_starts_with($line, '``')) {
            $is_inside_fenced_code_block = ! $is_inside_fenced_code_block;
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
                $warnings['Legacy markers'][] = sprintf('Legacy marker found in %s:%s Found "%s"', $filename, $index + 1, $marker);
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
            continue;
        }

        if (str_starts_with($link, '#')) {
            continue;
        }

        // Remove hash for anchors
        $link = explode('#', $link)[0];
        // Remove anything before spaces (image alt text)
        $link = explode(' ', $link)[0];

        // Add to new unique array
        $uniqueLinks[$link] = "$filename:$line";
    }
    foreach ($uniqueLinks as $link => $location) {
        $base = __DIR__.'/../../../docs';

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
        if (!file_exists($base.'/'.$link)) {
            $warnings['Broken links'][] = "Broken link to $link found in $location";
        }
    }
}

if (count($warnings) > 0) {
    echo "\n\033[31mWarnings:\033[0m \033[33m".count($warnings, COUNT_RECURSIVE)-count($warnings)." found \033[0m \n";
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
