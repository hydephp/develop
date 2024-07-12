<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

$raw = trim(shell_exec('git log --pretty=format:"%s"'));

// We now have an array of commits, starting with most recent
$lines = explode("\n", $raw);

// Now we filter it to remove duplicates, keeping only the earliest entry
$lines = array_unique($lines);

// Print the model
dump($lines);

// Save the model
file_put_contents(__DIR__ . '/commit-message-model.txt', implode("\n", $lines));
