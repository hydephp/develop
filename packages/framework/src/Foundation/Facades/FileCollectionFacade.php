<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Foundation\FileCollection;
use Hyde\Foundation\HydeKernel;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Hyde\Foundation\FileCollection
 */
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
