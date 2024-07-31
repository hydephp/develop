<?php

declare(strict_types=1);

namespace Hyde;

use Hyde\Foundation\HydeKernel;
use Illuminate\Support\Facades\Facade;
use JetBrains\PhpStorm\Pure;

/**
 * General facade for Hyde services.
 *
 * @see \Hyde\Foundation\HydeKernel
 *
 * @author  Caen De Silva <caen@desilva.se>
 * @copyright 2022 Caen De Silva
 * @license MIT License
 *
 * @mixin \Hyde\Foundation\HydeKernel
 *
 * @see \Hyde\Foundation\Concerns\ForwardsFilesystem
 * @see \Hyde\Foundation\Concerns\ForwardsHyperlinks
 */
class Hyde extends Facade
{
    /**
     * @psalm-return non-empty-string
     */
    public static function version(): string
    {
        return HydeKernel::version();
    }

    /**
     * @psalm-return HydeKernel
     */
    public static function getFacadeRoot(): HydeKernel
    {
        return HydeKernel::getInstance();
    }

    /**
     * @psalm-return HydeKernel
     */
    #[Pure]
    public static function kernel(): HydeKernel
    {
        return HydeKernel::getInstance();
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'hyde';
    }
}
