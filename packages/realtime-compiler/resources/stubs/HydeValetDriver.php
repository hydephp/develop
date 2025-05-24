<?php

namespace Valet\Drivers\Custom;

use Composer\InstalledVersions;
use Valet\Drivers\BasicValetDriver;

/**
 * @experimental This driver is experimental and may be unstable. Report issues at GitHub!
 *
 * @see https://github.com/hydephp/realtime-compiler/pull/30
 */
class HydeValetDriver extends BasicValetDriver
{
    /**
     * Determine if the driver serves the request.
     */
    public function serves(string $sitePath, string $siteName, string $uri): bool
    {
        return file_exists($sitePath.'/hyde');
    }

    /**
     * Take any steps necessary before loading the front controller for this driver.
     */
    public function beforeLoading(string $sitePath, string $siteName, string $uri): void
    {
        // Set the correct autoloader path for the realtime compiler
        putenv('HYDE_AUTOLOAD_PATH='.$sitePath.'/vendor/autoload.php');
    }

    /**
     * Determine if the incoming request is for a static file.
     */
    public function isStaticFile(string $sitePath, string $siteName, string $uri): false
    {
        return false; // Handled by the realtime compiler
    }

    /**
     * Get the fully resolved path to the application's front controller.
     */
    public function frontControllerPath(string $sitePath, string $siteName, string $uri): ?string
    {
        return $sitePath.'/vendor/hyde/realtime-compiler/bin/server.php';
    }

    /**
     * Get the logs paths for the application to show in Herds log viewer.
     */
    public function logFilesPaths(): array
    {
        return ['/storage/logs'];
    }

    /**
     * Display information about the application in the information tab of the Sites UI.
     */
    public function siteInformation(string $sitePath, string $phpBinary): array
    {
        $composerJson = json_decode(file_get_contents($sitePath.'/composer.json'), true);
        $hydeConfig = include $sitePath.'/config/hyde.php';

        return [
            'HydePHP Info' => [
                'Hyde Version' => InstalledVersions::isInstalled('hyde/hyde') ? InstalledVersions::getPrettyVersion('hyde/hyde') : 'Unknown',
                'Framework Version' => InstalledVersions::getPrettyVersion('hyde/framework') ?: 'Unknown',
                'Site Name' => $hydeConfig['name'] ?? 'Unknown',
                'Site URL' => $hydeConfig['url'] ?? 'Not set',
                'Site Language' => $hydeConfig['language'] ?? 'en',
                'Output Directory' => $hydeConfig['output_directory'] ?? '_site',
            ],
            'Build Info' => [
                'Pretty URLs' => $hydeConfig['pretty_urls'] ? 'Enabled' : 'Disabled',
                'Generate Sitemap' => $hydeConfig['generate_sitemap'] ? 'Yes' : 'No',
                'RSS Feed' => ($hydeConfig['rss']['enabled'] ?? false) ? 'Enabled' : 'Disabled',
                'Source Root' => $hydeConfig['source_root'] ?: '(Project Root)',
            ],
            'Features' => [
                'Enabled Features' => implode(', ', array_map(function ($feature) {
                    return $feature->name;
                }, $hydeConfig['features'] ?? [])),
            ],
            'Server Configuration' => [
                'Port' => $hydeConfig['server']['port'] ?? 8080,
                'Host' => $hydeConfig['server']['host'] ?? 'localhost',
                'Save Preview' => ($hydeConfig['server']['save_preview'] ?? true) ? 'Yes' : 'No',
                'Live Edit' => ($hydeConfig['server']['live_edit'] ?? false) ? 'Enabled' : 'Disabled',
                'Dashboard' => ($hydeConfig['server']['dashboard']['enabled'] ?? true) ? 'Enabled' : 'Disabled',
            ],
        ];
    }
}
