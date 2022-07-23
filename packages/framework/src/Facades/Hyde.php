<?php

namespace Hyde\Framework\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * General facade for Hyde services.
 *
 * @see \Hyde\Framework\Hyde
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
        return \Hyde\Framework\Hyde::class;
    }
}
