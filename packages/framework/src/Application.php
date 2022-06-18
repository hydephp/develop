<?php

namespace Hyde\Framework;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\PackageManifest;

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
