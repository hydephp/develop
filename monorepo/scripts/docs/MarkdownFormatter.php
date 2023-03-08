<?php

function find_markdown_files($dir) {
    $markdown_files = array();

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) == 'md') {
            $markdown_files[] = $file->getPathname();
        }
    }

    return $markdown_files;
}

$dir = '/path/to/directory';
$markdown_files = find_markdown_files($dir);

foreach ($markdown_files as $file) {
    echo $file . "\n";
}
