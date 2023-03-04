<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Hyde\Foundation\Kernel\PageCollection
 */
class Pages extends Facade
{
    public static function getPage(string $sourcePath): HydePage
    {
        return static::getFacadeRoot()->get($sourcePath) ?? throw new FileNotFoundException(message: "Page [$sourcePath] not found in page collection");
    }

    public static function getPages(?string $pageClass = null): PageCollection
    {
        return $pageClass ? static::getFacadeRoot()->filter(function (HydePage $page) use ($pageClass): bool {
            return $page instanceof $pageClass;
        }) : static::getFacadeRoot();
    }

    /** @return \Hyde\Foundation\Kernel\PageCollection<string, \Hyde\Pages\Concerns\HydePage> */
    public static function getFacadeRoot(): PageCollection
    {
        return HydeKernel::getInstance()->pages();
    }
}
