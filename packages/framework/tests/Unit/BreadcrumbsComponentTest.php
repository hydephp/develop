<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Views\Components\BreadcrumbsComponent;
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
}
