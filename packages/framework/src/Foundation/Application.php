<?php

declare(strict_types=1);

namespace Hyde\Framework\Foundation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\PackageManifest;

/**
 * @property mixed $app
 */
class Application extends \LaravelZero\Framework\Application
{
    /**
     * {@inheritdoc}
     */
    protected function registerBaseBindings(): void
    {
        parent::registerBaseBindings();

        /*
         * Enable auto-discovery.
         */
        $this->app->singleton(PackageManifest::class, function () {
            return new PackageManifest(
                new Filesystem,
                $this->basePath(),
                $this->basePath('storage/framework/cache/packages.php')
            );
        });
    }
}
