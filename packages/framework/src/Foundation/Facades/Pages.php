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
    /** @return \Hyde\Foundation\Kernel\PageCollection<string, \Hyde\Pages\Concerns\HydePage> */
    public static function getFacadeRoot(): PageCollection
    {
        return HydeKernel::getInstance()->pages();
    }
}
