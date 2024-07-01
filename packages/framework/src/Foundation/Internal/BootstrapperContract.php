<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

/**
 * @internal Bootstrap service defined in the console kernel.
 *
 * @see \LaravelZero\Framework\Contracts\BoostrapperContract [sic]
 */
interface BootstrapperContract
{
    /**
     * Performs a core task that needs to be performed on
     * early stages of the framework.
     *
     * @return void
     */
    public function bootstrap(\Illuminate\Contracts\Foundation\Application $app);
}
