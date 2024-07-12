<?php

declare(strict_types=1);

require_once __DIR__.'/../../vendor/autoload.php';

// Check if cache file exists
if (file_exists(__DIR__.'/commits.cache')) {
    $raw = file_get_contents(__DIR__.'/commits.cache');
} else {
    // All commits:
    // $raw = trim(shell_exec('git log --pretty=format:"%s"'));

    // Only on master branch
    $raw = trim(shell_exec('git log --pretty=format:"%s" --first-parent master'));
    file_put_contents(__DIR__.'/commits.cache', $raw);
}

// We now have an array of commits, starting with most recent
$lines = explode("\n", $raw);

$originalLineCount = count($lines);

// Now we filter it to remove duplicates, keeping only the earliest entry
$lines = filter($lines);

$lines = array_filter($lines, fn ($line) => ! str_starts_with($line, 'Merge'));
$lines = array_filter($lines, fn ($line) => ! str_starts_with($line, 'Revert'));
$lines = array_filter($lines, fn ($line) => ! str_starts_with($line, 'Reapply'));
$lines = array_filter($lines, fn ($line) => ! str_starts_with($line, 'Bump'));

// Print the model
dump($lines);

$newLineCount = count($lines);

$diff = $originalLineCount - $newLineCount;
// Print compression in percentage
$compression = number_format(100 - (($newLineCount / $originalLineCount) * 100), 2);
echo "Model compressed by {$compression}% (-{$diff} lines)\n";

// Save the model
file_put_contents(__DIR__.'/commit-message-model.txt', implode("\n", $lines));

function filter(array $lines): array
{
    $lines = array_reverse($lines);

    $filtered = [];
    $seen = [];

    foreach ($lines as $line) {
        if (! in_array($line, $seen, true)) {
            $seen[] = $line;
            $filtered[] = $line;
        }
    }

    return array_reverse($filtered);
}
