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
    $version = $argv[1] ?? null;
    if ($version === null) {
        $this->error('Missing version type (supply as first argument)');
        return 1;
    }
    /** @noinspection SpellCheckingInspection */
    if (! in_array($version, ['major', 'minor', 'patch', 'premajor', 'preminor', 'prepatch', 'prerelease'])) {
        $this->error('Invalid version type: ' . $version);
        $this->warning('Must be one of: major, minor, patch, premajor, preminor, prepatch, prerelease');
        return 1;
    }

    return 0;
}));
