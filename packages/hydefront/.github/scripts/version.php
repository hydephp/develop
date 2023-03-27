<?php

declare(strict_types=1);

require_once __DIR__ . '/minima.php';

exit(main(function (): int {
    if (! is_dir(getcwd() . '/packages')) {
        $this->error('This script must be run from the root of the monorepo');
        $this->warning('Current working directory: ' . getcwd());
        return 1;
    }

    global $argv;
    $versionType = $argv[1] ?? null;
    if ($versionType === null) {
        $this->error('Missing version type (supply as first argument)');
        return 1;
    }
    /** @noinspection SpellCheckingInspection */
    $nodeJsVersions = ['major', 'minor', 'patch', 'premajor', 'preminor', 'prepatch', 'prerelease'];
    if (! in_array($versionType, $nodeJsVersions)) {
        $this->error('Invalid version type: ' . $versionType);
        $this->warning('Must be one of: '.implode(', ', $nodeJsVersions));
        return 1;
    }

    $this->info("Creating a new HydeFront $versionType version...");
    $version = trim(shell_exec('npm version ' . $versionType . ' --no-git-tag-version'));
    $this->line("Updated package.json version to $version");

    return 0;
}));
