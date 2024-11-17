<?php

declare(strict_types=1);

require_once __DIR__ . '/minima.php';

exit(main(function (): int {
    $baseDir = __DIR__.'/../../';

    // If running in monorepo
    if (file_exists($baseDir.'../../composer.json') && str_contains(file_get_contents($baseDir.'../../composer.json'), 'hyde/monorepo')) {
        // Check that HydeFront entry in root package lock is up-to-date with the package.json
        $this->info('Verifying root package lock...');

        $rootPackageLock = json_decode(file_get_contents($baseDir.'../../package-lock.json'), true);
        $hydeFrontPackageLock = $rootPackageLock['dependencies']['hydefront'];
        $hydeFrontPackage = json_decode(file_get_contents($baseDir.'../../packages/hydefront/package.json'), true);
        $hydeFrontVersion = $hydeFrontPackage['version'];

        if (! $this->hasOption('skip-root-version-check')) {
            if ($hydeFrontPackageLock['version'] !== $hydeFrontVersion) {
                $this->error('Version mismatch in root package-lock.json and packages/hydefront/package.json:');
                $this->warning("Expected hydefront to have version '$hydeFrontPackageLock[version]', but found '$hydeFrontVersion'");
                $this->warning("Please run 'npm update hydefront'");
                return 1;
            } else {
                $this->info('Root package lock verified. All looks good!');
                $this->line();
            }
        }
    }

    if ($this->hasOption('inject-version')) {
        $package = json_decode(file_get_contents($baseDir.'package.json'), true);
        $version = $package['version'];
        
        return 0;
    }

    $this->info('Verifying build files...');
    $exitCode = 0;

    $package = json_decode(file_get_contents($baseDir.'package.json'), true);
    $version = $package['version'];
    $this->line("Found version '$version' in package.json");

    $appCssVersion = getCssVersion($baseDir.'dist/app.css');
    $this->line("Found version '$appCssVersion' in dist/app.css");

    if ($this->hasOption('fix')) {
        $this->info('Fixing build files...');

        if ($version !== $appCssVersion) {
            $this->line(' > Updating dist/app.css...');
            $contents = file_get_contents($baseDir.'dist/app.css');
            $contents = str_replace($appCssVersion, $version, $contents);
            file_put_contents($baseDir.'dist/app.css', $contents);
            $filesChanged = true;
        }

        if (isset($filesChanged)) {
            $this->info('Build files fixed');
            $this->info('Tip: You may want to verify the changes again.');
        } else {
            $this->warning('Nothing to fix!');
        }
        return 0;
    }

    if ($version !== $appCssVersion) {
        $this->error('Version mismatch in package.json and dist/app.css:');
        $this->warning("Expected app.css to have version '$version', but found '$appCssVersion'");
        $exitCode = 1;
    }

    if ($exitCode > 0) {
        $this->error('Exiting with errors.');
    } else {
        $this->info('Build files verified. All looks good!');
    }

    return $exitCode;
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
