<?php

namespace Hyde\Framework;

use Illuminate\Support\Facades\Facade;

/**
 * General facade for Hyde services.
 *
 * @see \Hyde\Framework\HydeKernel
 *
 * @author  Caen De Silva <caen@desilva.se>
 * @copyright 2022 Caen De Silva
 * @license MIT License
 *
 * @link https://hydephp.com/
 */
class Hyde extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return HydeKernel::class;
    }

    public static function version()
    {
        return HydeKernel::version();
    }
}
