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
    protected function setUp(): void
    {
        self::setupKernel();
        self::mockConfig();
    }

    public function test_file_collection_facade()
    {
        $this->assertSame(HydeKernel::getInstance()->files(), Files::getInstance());
    }

    public function test_page_collection_facade()
    {
        $this->assertSame(HydeKernel::getInstance()->pages(), Pages::getInstance());
    }

    public function test_route_collection_facade()
    {
        $this->assertSame(HydeKernel::getInstance()->routes(), Routes::getInstance());
    }

    public function test_facade_roots()
    {
        $this->assertSame(Files::getInstance(), Files::getFacadeRoot());
        $this->assertSame(Pages::getInstance(), Pages::getFacadeRoot());
        $this->assertSame(Routes::getInstance(), Routes::getFacadeRoot());
    }
}
