<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Foundation\FileCollection;
use Hyde\Foundation\HydeKernel;
use Illuminate\Support\Facades\Facade;

class FileCollectionFacade extends Facade
{
    public static function getFacadeRoot(): FileCollection
    {
        return HydeKernel::getInstance()->files();
    }
}
