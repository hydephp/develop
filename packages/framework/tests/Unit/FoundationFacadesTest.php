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

/**
 * @covers \Hyde\Foundation\Facades\Files
 * @covers \Hyde\Foundation\Facades\Pages
 * @covers \Hyde\Foundation\Facades\Routes
 */
class FoundationFacadesTest extends UnitTestCase
{
    public static function setupBeforeClass(): void
    {
        self::needsKernel();
        self::mockConfig();
    }

    public function testFilesFacade()
    {
        $this->assertInstanceOf(FileCollection::class, Files::getInstance());
    }

    public function testPagesFacade()
    {
        $this->assertInstanceOf(PageCollection::class, Pages::getInstance());
    }

    public function testRoutesFacade()
    {
        $this->assertInstanceOf(RouteCollection::class, Routes::getInstance());
    }

    public function testFilesFacadeUsesKernelInstance()
    {
        $this->assertSame(HydeKernel::getInstance()->files(), Files::getInstance());
    }

    public function testPagesFacadeUsesKernelInstance()
    {
        $this->assertSame(HydeKernel::getInstance()->pages(), Pages::getInstance());
    }

    public function testRoutesFacadeUsesKernelInstance()
    {
        $this->assertSame(HydeKernel::getInstance()->routes(), Routes::getInstance());
    }

    public function testFilesFacadeRoot()
    {
        $this->assertSame(Files::getInstance(), Files::getFacadeRoot());
    }

    public function testPagesFacadeRoot()
    {
        $this->assertSame(Pages::getInstance(), Pages::getFacadeRoot());
    }

    public function testRoutesFacadeRoot()
    {
        $this->assertSame(Routes::getInstance(), Routes::getFacadeRoot());
    }
}
