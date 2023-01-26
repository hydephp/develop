<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

/**
 * @internal
 */
class Kernel extends \Illuminate\Foundation\Console\Kernel
{
    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers()
    {
        $array = array_combine(parent::bootstrappers(), parent::bootstrappers());
        $array[\Illuminate\Foundation\Bootstrap\LoadConfiguration::class] = \Hyde\Foundation\Internal\LoadConfiguration::class;

        return array_values($array);
    }
}
