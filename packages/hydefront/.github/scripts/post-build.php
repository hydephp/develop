<?php

declare(strict_types=1);

require_once __DIR__ . '/minima.php';

main(function (): int {
    $this->info('Verifying build files...');

    $baseDir = __DIR__ . '/../../';
    $package = json_decode(file_get_contents($baseDir . 'package.json'), true);
    $version = $package['version'];
    $this->line("Found version '$version' in package.json");

    $hydeCssVersion = getCssVersion($baseDir . 'dist/hyde.css');
    $this->line("Found version '$hydeCssVersion' in hyde.css");

    $appCssVersion = getCssVersion($baseDir . 'dist/app.css');
    $this->line("Found version '$appCssVersion' in app.css");

    if ($this->hasOption('fix')) {
        $this->info('Fixing build files...');
    }

    return 0;
});

function getCssVersion(string $path): string
{
    $contents = file_get_contents($path);
    $prefix = '/*! HydeFront v';
    if (! str_starts_with($contents, $prefix)) {
        throw new Exception('Invalid CSS file');
    }
    $contents = substr($contents, strlen($prefix));
    // Get everything before  |
    $pipePos = strpos($contents, '|');
    if ($pipePos === false) {
        throw new Exception('Invalid CSS file');
    }
    $contents = substr($contents, 0, $pipePos);
    return trim($contents);
}
