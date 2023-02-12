<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Foundation\HydeKernel;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Hyde\Foundation\Kernel\PageCollection
 */
class PageCollection extends Facade
{
    public static function getFacadeRoot(): \Hyde\Foundation\Kernel\PageCollection
    {
        return HydeKernel::getInstance()->pages();
    }
}
