<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\Facades\Files;
use Hyde\Foundation\Facades\Pages;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Foundation\HydeKernel;
use Hyde\Testing\UnitTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\Facades\Files::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\Facades\Pages::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\Facades\Routes::class)]
class FoundationFacadesTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    public function testFilesFacade()
    {
        $this->assertInstanceOf(FileCollection::class, Files::getFacadeRoot());
    }

    public function testPagesFacade()
    {
        $this->assertInstanceOf(PageCollection::class, Pages::getFacadeRoot());
    }

    public function testRoutesFacade()
    {
        $this->assertInstanceOf(RouteCollection::class, Routes::getFacadeRoot());
    }

    public function testFilesFacadeUsesKernelInstance()
    {
        $this->assertSame(HydeKernel::getInstance()->files(), Files::getFacadeRoot());
    }

    public function testPagesFacadeUsesKernelInstance()
    {
        $this->assertSame(HydeKernel::getInstance()->pages(), Pages::getFacadeRoot());
    }

    public function testRoutesFacadeUsesKernelInstance()
    {
        $this->assertSame(HydeKernel::getInstance()->routes(), Routes::getFacadeRoot());
    }
}
