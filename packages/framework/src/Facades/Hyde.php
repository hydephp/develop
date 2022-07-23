<?php

namespace Hyde\Framework\Facades;

use Illuminate\Support\Facades\Facade;

class Hyde extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Hyde\Framework\Hyde::class;
    }
}
