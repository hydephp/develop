#!/usr/bin/env php
<?php

// Internal helper to speed up branching up cherry picked commits for pull requests

// Check if we have the correct number of arguments
if ($argc !== 3 && $argc !== 4) {
    echo "\033[31mError: Invalid number of arguments\033[0m\n";
    echo "\033[33mUsage:\033[0m php bin/pick.php <commit-hash> <branch-name> [--pretend]\n";
    echo "\033[33mExample:\033[0m php bin/pick.php abc123 feature-branch\n";
    exit(1);
}

// Get arguments
$hash = $argv[1];
$branch = $argv[2];
$pretend = ($argv[3] ?? false) === '--pretend';

// Get the commit message
exec("git show $hash --pretty=format:\"%s%n%b\" -s", $output, $returnCode);

if ($returnCode === 0 && !empty($output)) {
    $commitMessage = implode("\n", $output);

    // Check if this matches the subrepo sync format
    if (preg_match('/^Merge pull request #(\d+).*\n(.*?)https:\/\/github\.com\/hydephp\/develop\/commit/', $commitMessage, $matches)) {
        $prNumber = $matches[1];
        $title = trim($matches[2]);
        $body = "Merges pull request https://github.com/hydephp/develop/pull/$prNumber";

        $printWhenDone = "\n\033[33mSuggested PR format: (Line 1: title, Line 2: description, Line 3: command)\033[0m\n";
        $printWhenDone .= "$title\n$body\n";

        $printWhenDone .= "\033[37mgh pr create --title \"$title\" --body \"$body\"\033[0m\n";
    }
}

// Create new branch from master
exec(($pretend ? 'echo ' : '') . "git checkout -b $branch master", $output, $returnCode);

if ($returnCode !== 0) {
    echo "\033[31mError creating new branch: $branch\033[0m\n";
    exit(1);
}

// Cherry-pick the commit
exec(($pretend ? 'echo ' : '') . "git cherry-pick $hash", $output, $returnCode);

if ($returnCode !== 0) {
    echo "\033[31mError cherry-picking commit: $hash\033[0m\n";
    exit(1);
}

echo "\033[32mSuccessfully created branch '$branch' and cherry-picked commit '$hash'\033[0m\n";

if (isset($printWhenDone)) {
    echo $printWhenDone;
}
