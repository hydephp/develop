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
 * @author  Emma De Silva <emma@desilva.se>
 * @copyright 2022 Emma De Silva
 * @license MIT License
 *
 * @mixin \Hyde\Foundation\HydeKernel
 *
 * @see \Hyde\Foundation\Concerns\ForwardsFilesystem
 * @see \Hyde\Foundation\Concerns\ForwardsHyperlinks
 */
class Hyde extends Facade
{
    public static function version(): string
    {
        return HydeKernel::version();
    }

    public static function getFacadeRoot(): HydeKernel
    {
        return HydeKernel::getInstance();
    }

    #[Pure]
    public static function kernel(): HydeKernel
    {
        return HydeKernel::getInstance();
    }
}
