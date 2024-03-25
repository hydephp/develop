<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Pages\InMemoryPage;
use Hyde\Testing\UnitTestCase;
use Hyde\Support\Models\Route;
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
        $this->assertSame($page, $this->kernel->routes()->first()->getPage());
    }

    public function testWithPagesReplacesExistingRoutes()
    {
        $this->withPages([new InMemoryPage('foo')]);
        $this->assertSame(['foo'], $this->getRouteKeys());

        $this->withPages([new InMemoryPage('bar')]);
        $this->assertSame(['bar'], $this->getRouteKeys());
    }

    protected function getRouteKeys()
    {
        return $this->kernel->routes()->map(fn (Route $route) => $route->getRouteKey())->all();
    }
}
