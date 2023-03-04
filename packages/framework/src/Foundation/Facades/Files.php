<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Support\Filesystem\SourceFile;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Hyde\Foundation\Kernel\FileCollection
 */
class Files extends Facade
{
    public static function getFile(string $filePath): SourceFile
    {
        return static::getFacadeRoot()->get($filePath) ?? throw new FileNotFoundException(message: "File [$filePath] not found in file collection");
    }

    /**
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>|null  $pageClass
     * @return \Hyde\Foundation\Kernel\FileCollection<string, \Hyde\Support\Filesystem\SourceFile>
     */
    public static function getFiles(?string $pageClass = null): FileCollection
    {
        return $pageClass ? static::getFacadeRoot()->where(function (SourceFile $file) use ($pageClass): bool {
            return $file->model === $pageClass;
        }) : static::getFacadeRoot();
    }

    /**  @return \Hyde\Foundation\Kernel\FileCollection<string, \Hyde\Support\Filesystem\SourceFile> */
    public static function getFacadeRoot(): FileCollection
    {
        return HydeKernel::getInstance()->files();
    }
}
