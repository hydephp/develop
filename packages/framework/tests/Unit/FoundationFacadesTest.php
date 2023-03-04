<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\Facades\Files;
use Hyde\Foundation\Facades\Pages;
use Hyde\Foundation\Facades\Routes;
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

    public function testFilesFacadeIsMockable()
    {
        Files::shouldReceive('getFacadeRoot')->andReturn('mocked');
    }

    public function testPagesFacadeIsMockable()
    {
        Pages::shouldReceive('getFacadeRoot')->andReturn('mocked');
    }

    public function testRoutesFacadeIsMockable()
    {
        Routes::shouldReceive('getFacadeRoot')->andReturn('mocked');
    }
}
