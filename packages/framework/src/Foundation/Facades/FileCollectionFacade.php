<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\FileCollection;
use Illuminate\Support\Facades\Facade;

class FileCollectionFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FileCollection::class;
    }

    public static function getFacadeRoot(): FileCollection
    {
        return HydeKernel::getInstance()->files();
    }
}
