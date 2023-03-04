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
    public static function getFacadeRoot(): PageCollection
    {
        return HydeKernel::getInstance()->pages();
    }

    public static function getPage(string $sourcePath): HydePage
    {
        return static::getFacadeRoot()->get($sourcePath) ?? throw new FileNotFoundException($sourcePath . ' in page collection');
    }

    public static function getPages(?string $pageClass = null): PageCollection
    {
        return $pageClass ? static::getFacadeRoot()->filter(function (HydePage $page) use ($pageClass): bool {
            return $page instanceof $pageClass;
        }) : static::getFacadeRoot();
    }

    /**
     * This method adds the specified page to the page collection.
     * It can be used by package developers to add a page that will be compiled.
     *
     * Note that this method when used outside of this class is only intended to be used for adding on-off pages;
     * If you are registering multiple pages, you may instead want to register an entire custom page class,
     * as that will allow you to utilize the full power of the HydePHP autodiscovery.
     *
     * When using this method, take notice of the following things:
     * 1. Be sure to register the page before the HydeKernel boots,
     *    otherwise it might not be fully processed by Hyde.
     * 2. Note that all pages will have their routes added to the route index,
     *    and subsequently be compiled during the build process.
     */
    public static function addPage(HydePage $page): PageCollection
    {
        static::getFacadeRoot()->put($page->getSourcePath(), $page);

        return static::getFacadeRoot();
    }
}
