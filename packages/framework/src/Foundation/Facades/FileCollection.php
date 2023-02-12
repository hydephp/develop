<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Foundation\HydeKernel;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Hyde\Foundation\Kernel\FileCollection
 */
class FileCollection extends Facade
{
    public static function getFacadeRoot(): \Hyde\Foundation\Kernel\FileCollection
    {
        return HydeKernel::getInstance()->files();
    }
}
