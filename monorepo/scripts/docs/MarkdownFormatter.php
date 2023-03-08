<?php

declare(strict_types=1);

$timeStart = microtime(true);

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
}

function add_two_lines_above_headings($filename)
{
    $lines = file($filename);
    $new_lines = array();

    $previous_line_is_heading = false;
    foreach ($lines as $line) {
        if (preg_match('/^#+\s/', $line)) {
            if (!$previous_line_is_heading) {
                $new_lines[] = "\n";
                $new_lines[] = "\n";
            }
            $previous_line_is_heading = true;
        } else {
            $previous_line_is_heading = false;
        }
        $new_lines[] = $line;
    }

    $new_content = implode('', $new_lines);
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

echo 'Done in '.$time.'ms';
