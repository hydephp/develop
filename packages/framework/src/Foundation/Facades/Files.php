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

    /**
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>|null  $pageClass
     * @return \Hyde\Foundation\Kernel\FileCollection<\Hyde\Support\Filesystem\SourceFile>
     */
    public static function getSourceFiles(?string $pageClass = null): FileCollection
    {
        //
    }

    /**
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>  $pageClass
     * @return \Hyde\Foundation\Kernel\FileCollection<\Hyde\Support\Filesystem\SourceFile>
     */
    public static function getSourceFilesFor(string $pageClass): FileCollection
    {
        //
    }

    /** @return \Hyde\Foundation\Kernel\FileCollection<\Hyde\Support\Filesystem\SourceFile> */
    public static function getAllSourceFiles(): FileCollection
    {
        //
    }

    /** @return \Hyde\Foundation\Kernel\FileCollection<\Hyde\Support\Filesystem\MediaFile> */
    public static function getMediaFiles(): FileCollection
    {
        //
    }
}
