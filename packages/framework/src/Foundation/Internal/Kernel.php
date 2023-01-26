<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

/**
 * @internal
 */
class Kernel extends \LaravelZero\Framework\Kernel
{
    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers()
    {
        $array = array_combine(parent::bootstrappers(), parent::bootstrappers());
        $array[\LaravelZero\Framework\Bootstrap\LoadConfiguration::class] = \Hyde\Foundation\Internal\LoadConfiguration::class;

        return array_values($array);
    }
}
