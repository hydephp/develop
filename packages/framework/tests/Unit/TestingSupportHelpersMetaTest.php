<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use AllowDynamicProperties;
use Hyde\Pages\InMemoryPage;
use Hyde\Testing\UnitTestCase;
use Hyde\Testing\MocksKernelFeatures;
use Hyde\Testing\FluentTestingHelpers;

/**
 * Meta test for internal testing helpers.
 *
 * @see \Hyde\Testing\Support
 * @see \Hyde\Testing\MocksKernelFeatures
 */
#[AllowDynamicProperties]
#[\PHPUnit\Framework\Attributes\CoversNothing]
class TestingSupportHelpersMetaTest extends UnitTestCase
{
    use MocksKernelFeatures;
    use FluentTestingHelpers;

    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    public function testWithPages()
    {
        $page = new InMemoryPage('foo');

        $this->withPages([$page]);

        $this->assertSame(['foo' => $page], $this->kernel->pages()->all());
        $this->assertEquals(['foo' => $page->getRoute()], $this->kernel->routes()->all());
    }

    public function testWithPagesReplacesExistingPages()
    {
        $this->withPages([new InMemoryPage('foo')]);
        $this->assertSame(['foo'], $this->getPageIdentifiers());

        $this->withPages([new InMemoryPage('bar')]);
        $this->assertSame(['bar'], $this->getPageIdentifiers());
    }

    public function testWithPagesReplacesExistingRoutes()
    {
        $this->withPages([new InMemoryPage('foo')]);
        $this->assertSame(['foo'], $this->getRouteKeys());

        $this->withPages([new InMemoryPage('bar')]);
        $this->assertSame(['bar'], $this->getRouteKeys());
    }

    public function testWithPagesWhenSupplyingStrings()
    {
        $this->withPages(['foo', 'bar', 'baz']);

        $this->assertSame(['foo', 'bar', 'baz'], $this->getRouteKeys());
        $this->assertSame(['foo', 'bar', 'baz'], $this->getPageIdentifiers());

        $this->assertContainsOnlyInstancesOf(InMemoryPage::class, $this->kernel->pages());
    }

    public function testAssertAllSameAssertsAllValuesAreTheSame()
    {
        $string = 'foo';
        $array = ['foo'];
        $object = (object) ['foo' => 'bar'];

        $this->assertAllSame($string, 'foo', 'foo');
        $this->assertAllSame($array, $array, $array);
        $this->assertAllSame($object, $object, $object);
    }

    public function testAssertAllSameFailsWhenValuesAreNotEqual()
    {
        $tests = [
            ['foo', 'bar'],
            [['foo'], ['bar']],
            [(object) ['foo' => 'bar'], (object) ['foo' => 'baz']],
        ];

        foreach ($tests as $expected) {
            try {
                $this->assertAllSame(...$expected);
            } catch (\PHPUnit\Framework\AssertionFailedError $exception) {
                $this->assertStringContainsString('Failed asserting that two', $exception->getMessage());
                $this->assertStringContainsString('are equal.', $exception->getMessage());
            }
        }
    }

    public function testAssertAllSameFailsWhenValuesAreNotIdentical()
    {
        try {
            $this->assertAllSame((object) ['foo' => 'bar'], (object) ['foo' => 'bar']);
        } catch (\PHPUnit\Framework\AssertionFailedError $exception) {
            $this->assertSame('Failed asserting that two variables reference the same object.', $exception->getMessage());
        }
    }

    protected function getPageIdentifiers()
    {
        return $this->kernel->pages()->keys()->all();
    }

    protected function getRouteKeys(): array
    {
        return $this->kernel->routes()->keys()->all();
    }
}
