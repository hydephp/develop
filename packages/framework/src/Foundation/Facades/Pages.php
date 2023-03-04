<?php

declare(strict_types=1);

namespace Hyde\Foundation\Facades;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\PageCollection;
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

    public function getPage(string $sourcePath): HydePage
    {
        return static::getFacadeRoot()->getPage($sourcePath);
    }

    public function getPages(?string $pageClass = null): PageCollection
    {
        return static::getFacadeRoot()->getPages($pageClass);
    }

    public function addPage(HydePage $page): PageCollection
    {
        return static::getFacadeRoot()->addPage($page);
    }
}
