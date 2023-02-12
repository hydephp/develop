<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\PageCollection;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Hyde\Foundation\Kernel\PageCollection
 */
class Pages extends Facade
{
    public static function getFacadeRoot(): PageCollection
    {
        return HydeKernel::getInstance()->pages();
    }
}
