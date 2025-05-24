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
        
        // Support for package-lock.json v3 format
        $hydeFrontPackageLock = null;
        $lockfileVersion = $rootPackageLock['lockfileVersion'] ?? 1;
        
        if ($lockfileVersion >= 3) {
            // v3+ uses 'packages' with node_modules paths as keys
            $hydeFrontPackageLock = $rootPackageLock['packages']['node_modules/hydefront'] ?? null;
        } else {
            // v1 and v2 use 'dependencies' at root level
            $hydeFrontPackageLock = $rootPackageLock['dependencies']['hydefront'] ?? null;
        }
        
        $hydeFrontPackage = json_decode(file_get_contents($baseDir.'../../packages/hydefront/package.json'), true);
        $hydeFrontVersion = $hydeFrontPackage['version'];

        if (! $this->hasOption('skip-root-version-check')) {
            if (!$hydeFrontPackageLock) {
                $this->error('Could not find hydefront in root package-lock.json');
                $this->warning("Please run 'npm update hydefront'");
                return 1;
            } elseif ($hydeFrontPackageLock['version'] !== $hydeFrontVersion) {
                $this->error('Version mismatch in root package-lock.json and packages/hydefront/package.json:');
                $this->warning("Expected hydefront to have version '$hydeFrontPackageLock[version]', but found '$hydeFrontVersion'");
                $this->warning("Please run 'npm update hydefront'");
                return 1;
            } else {
                $this->info('Root package lock verified. All looks good!');
            }
        }
    }

    return 0;
}));
