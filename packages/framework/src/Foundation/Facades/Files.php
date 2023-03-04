<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Support\Filesystem\ProjectFile;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Hyde\Foundation\Kernel\FileCollection
 */
class Files extends Facade
{
    public static function getFacadeRoot(): FileCollection
    {
        return HydeKernel::getInstance()->files();
    }

    public static function getFile(string $filePath): ProjectFile
    {
        return static::getFacadeRoot()->get($filePath) ?? throw new FileNotFoundException($filePath.' in file collection');
    }
}
