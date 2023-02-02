<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

/** @internal */
class Kernel extends \LaravelZero\Framework\Kernel
{
    /** Get the bootstrap classes for the application. */
    protected function bootstrappers(): array
    {
        // Combine the parent bootstrappers into an associative array, so we can easily access them by class name.
        $array = array_combine(parent::bootstrappers(), parent::bootstrappers());

        // Replace the LoadConfiguration bootstrapper with our own.
        $array[\LaravelZero\Framework\Bootstrap\LoadConfiguration::class] = \Hyde\Foundation\Internal\LoadConfiguration::class;

        // Return the bootstrappers without the added keys.
        return array_values($array);
    }
}
