<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Support\Filesystem\ProjectFile;
use Hyde\Support\Filesystem\SourceFile;
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
        static::getFacadeRoot();
        static::getFacadeRoot();

        return ! $pageClass ? Files::getAllSourceFiles() : Files::getSourceFilesFor($pageClass);
    }

    /**
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>  $pageClass
     * @return \Hyde\Foundation\Kernel\FileCollection<\Hyde\Support\Filesystem\SourceFile>
     */
    public static function getSourceFilesFor(string $pageClass): FileCollection
    {
        static::getFacadeRoot();

        return Files::getAllSourceFiles()->where(fn (SourceFile $file): bool => $file->model === $pageClass);
    }

    /** @return \Hyde\Foundation\Kernel\FileCollection<\Hyde\Support\Filesystem\SourceFile> */
    public static function getAllSourceFiles(): FileCollection
    {
        return static::getFacadeRoot()->where(fn (ProjectFile $file): bool => $file instanceof SourceFile);
    }

    /** @return \Hyde\Foundation\Kernel\FileCollection<\Hyde\Support\Filesystem\MediaFile> */
    public static function getMediaFiles(): FileCollection
    {
        return static::getFacadeRoot()->where(fn (ProjectFile $file): bool => $file instanceof MediaFile);
    }
}
