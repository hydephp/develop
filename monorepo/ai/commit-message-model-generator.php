<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

// Check if cache file exists
if (file_exists(__DIR__ . '/commits.txt')) {
    $raw = file_get_contents(__DIR__ . '/commits.txt');
} else {
    $raw = trim(shell_exec('git log --pretty=format:"%s"'));
    file_put_contents(__DIR__ . '/commits.txt', $raw);
}

// We now have an array of commits, starting with most recent
$lines = explode("\n", $raw);

// Now we filter it to remove duplicates, keeping only the earliest entry
$lines = array_unique($lines);

// Print the model
dump($lines);

// Save the model
file_put_contents(__DIR__ . '/commit-message-model.txt', implode("\n", $lines));
