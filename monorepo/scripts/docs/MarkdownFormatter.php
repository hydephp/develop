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
