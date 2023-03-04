<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\Facades\Files;
use Hyde\Foundation\Facades\Pages;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\HydeKernel;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\Facades\Files
 * @covers \Hyde\Foundation\Facades\Pages
 * @covers \Hyde\Foundation\Facades\Routes
 */
class FoundationFacadesTest extends TestCase
{
    public function test_file_collection_facade()
    {
        $this->assertSame(
            HydeKernel::getInstance()->files(),
            Files::getInstance()
        );

        Hyde::files();
        $this->assertEquals(
            Files::getSourceFiles(null),
            Files::getSourceFiles()
        );
    }

    public function test_page_collection_facade()
    {
        $this->assertSame(
            HydeKernel::getInstance()->pages(),
            Pages::getInstance()
        );

        Hyde::pages();
        $this->assertEquals(
            Pages::getPages(null),
            Pages::getPages()
        );
    }

    public function test_route_collection_facade()
    {
        $this->assertSame(
            HydeKernel::getInstance()->routes(),
            Routes::getInstance()
        );

        Hyde::routes();
        $this->assertEquals(
            Routes::getRoutes(null),
            Routes::getRoutes()
        );
    }

    public function test_facade_roots()
    {
        $this->assertSame(Files::getInstance(), Files::getFacadeRoot());
        $this->assertSame(Pages::getInstance(), Pages::getFacadeRoot());
        $this->assertSame(Routes::getInstance(), Routes::getFacadeRoot());
    }
}
