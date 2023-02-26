<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Views\Components\BreadcrumbsComponent;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Framework\Views\Components\BreadcrumbsComponent
 */
class BreadcrumbsComponentTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
        self::mockConfig();
    }

    public function testCanConstruct()
    {
        Render::shouldReceive('getCurrentRoute')->once()->andReturn(new Route(new MarkdownPage()));

        $this->assertInstanceOf(BreadcrumbsComponent::class, new BreadcrumbsComponent());
    }
}
