<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

// Check if cache file exists
if (file_exists(__DIR__ . '/commits.cache')) {
    $raw = file_get_contents(__DIR__ . '/commits.cache');
} else {
    $raw = trim(shell_exec('git log --pretty=format:"%s"'));
    file_put_contents(__DIR__ . '/commits.cache', $raw);
}

// We now have an array of commits, starting with most recent
$lines = explode("\n", $raw);

$originalLineCount = count($lines);

// Now we filter it to remove duplicates, keeping only the earliest entry
$lines = array_unique($lines);

// Print the first 100 and last 100 lines in the model
$dump = array_slice($lines, 0, 100) + array_slice($lines, -100);
dump($dump);

$newLineCount = count($lines);

$diff = $originalLineCount - $newLineCount;
// Print compression in percentage
$compression = number_format(100 - (($newLineCount / $originalLineCount) * 100), 2);
echo "Model compressed by {$compression}% (-{$diff} lines)\n";

// Save the model
file_put_contents(__DIR__ . '/commit-message-model.txt', implode("\n", $lines));
