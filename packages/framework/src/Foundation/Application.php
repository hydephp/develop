<?php

declare(strict_types=1);

namespace Hyde\Foundation;

/**
 * @property self $app
 */
class Application extends \LaravelZero\Framework\Application
{
    protected $storagePath = 'app/storage';

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
     */
    public function getCachedPackagesPath(): string
    {
        // Since we have a custom path for the cache directory, we need to return it here.
        return 'app/storage/framework/cache/packages.php';
    }
}
