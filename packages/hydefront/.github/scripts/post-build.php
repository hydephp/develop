<?php

declare(strict_types=1);

require_once __DIR__ . '/minima.php';

main(function (): int {
    $this->info('Verifying build files...');

    $baseDir = __DIR__ . '/../../';
    $package = json_decode(file_get_contents($baseDir . 'package.json'), true);
    $version = $package['version'];
    $this->line("Found version: $version");

    if ($this->hasOption('fix')) {
        $this->info('Fixing build files...');
    }

    return 0;
});
