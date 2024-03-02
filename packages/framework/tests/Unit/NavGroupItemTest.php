<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\RenderData;

/**
 * @covers \Hyde\Framework\Features\Navigation\NavGroupItem
 */
class NavGroupItemTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::$hasSetUpKernel = false;

        self::needsKernel();
        self::mockConfig();
    }

    protected function setUp(): void
    {
        Render::swap(new RenderData());
    }
}
