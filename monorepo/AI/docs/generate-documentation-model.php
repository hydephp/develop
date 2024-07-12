<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

// This script generates a single .txt model of all HydePHP documentation

$timeStart = microtime(true);

$model = 'Start HydePHP Documentation (Framework version v'.\Hyde\Foundation\HydeKernel::VERSION . ")\n\n";

// Iterate through all files in the documentation directory, recursively
$directory = new RecursiveDirectoryIterator(__DIR__ . '/../../../docs');
$iterator = new RecursiveIteratorIterator($directory);
$files = new RegexIterator($iterator, '/^.+\.md$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($files as $file) {
    $baseDirRelativeToDocs = (str_replace(__DIR__ . '/../../../docs/', '', dirname($file[0])));
    $relativePathName = str_replace(__DIR__ . '/../../../docs/', '', $file[0]);

    if ($baseDirRelativeToDocs === 'redirects' || str_starts_with($baseDirRelativeToDocs, '_')) {
        continue;
    }

    $contents = file_get_contents($file[0]);
    // Strip front matter if present
    $contents = preg_replace('/^---(.|\n)*---\n/', '', $contents);

    $model .= '--- ' . $relativePathName . " ---\n\n" . $contents . "\n\n";
}

// Write the model to a file
file_put_contents(__DIR__ . '/model.txt', $model);

$timeEnd = microtime(true);
$time = $timeEnd - $timeStart;
$timeMs = number_format($time * 1000);

$modelSizeKb = number_format(filesize(__DIR__ . '/model.txt') / 1024, 2);

echo "Model generated in {$timeMs}ms ({$modelSizeKb} KB)\n";
