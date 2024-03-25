<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Pages\InMemoryPage;
use Hyde\Testing\UnitTestCase;
use Hyde\Testing\MocksKernelFeatures;

/**
 * Meta test for internal testing helpers.
 *
 * @see \Hyde\Testing\Support
 * @see \Hyde\Testing\MocksKernelFeatures
 *
 * @coversNothing
 */
class TestingSupportHelpersMetaTest extends UnitTestCase
{
    use MocksKernelFeatures;

    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    public function testWithPages()
    {
        $page = new InMemoryPage('foo');

        $this->withPages([$page]);

        $this->assertEquals([$page->getRoute()], $this->kernel->routes()->all());
    }
}
