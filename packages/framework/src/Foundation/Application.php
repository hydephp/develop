<?php

declare(strict_types=1);

namespace Hyde\Foundation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\PackageManifest;

/**
 * @property self $app
 */
class Application extends \LaravelZero\Framework\Application
{
    /**
     * {@inheritdoc}
     */
    protected function registerBaseBindings(): void
    {
        // Laravel Zero disables auto-discovery, but we want to use it,
        // so we'll call the grandparent's method instead of the parent's.
        \Illuminate\Foundation\Application::registerBaseBindings();
    }

    /**
     * Get the path to the cached packages.php file.
     *
     * @return string
     */
    public function getCachedPackagesPath()
    {
        // Since we have a custom path for the cache directory, we need to return it here.
        return 'storage/framework/cache/packages.php';
    }
}
