<?php

declare(strict_types=1);

require_once __DIR__ . '/minima.php';

exit(main(function (): int {
    $this->info('Verifying build files...');

    $baseDir = __DIR__.'/../../';
    $package = json_decode(file_get_contents($baseDir.'package.json'), true);
    $version = $package['version'];
    $this->line("Found version '$version' in package.json");

    $hydeCssVersion = getCssVersion($baseDir.'dist/hyde.css');
    $this->line("Found version '$hydeCssVersion' in dist/hyde.css");

    $appCssVersion = getCssVersion($baseDir.'dist/app.css');
    $this->line("Found version '$appCssVersion' in dist/app.css");

    if ($version !== $hydeCssVersion) {
        $this->error('Version mismatch in package.json and dist/hyde.css:');
        $this->warning("Expected hyde.css to have version '$version', but found '$hydeCssVersion'");
        $exitCode = 1;
    }

    if ($version !== $appCssVersion) {
        $this->error('Version mismatch in package.json and dist/app.css:');
        $this->warning("Expected app.css to have version '$version', but found '$appCssVersion'");
        $exitCode = 1;
    }

    if ($this->hasOption('fix')) {
        $this->info('Fixing build files...');

        $this->line(' > Updating dist/hyde.css...');
        $contents = file_get_contents($baseDir.'dist/hyde.css');
        $contents = str_replace($hydeCssVersion, $version, $contents);
        file_put_contents($baseDir.'dist/hyde.css', $contents);

        $this->line(' > Updating dist/app.css...');
        $contents = file_get_contents($baseDir.'dist/app.css');
        $contents = str_replace($appCssVersion, $version, $contents);
        file_put_contents($baseDir.'dist/app.css', $contents);

        $this->info('Build files fixed');
    }

    return $exitCode ?? 0;
}));

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
