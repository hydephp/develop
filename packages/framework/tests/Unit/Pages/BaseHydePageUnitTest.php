<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Exception;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Testing\TestCase;

/**
 * This extendable base text class provides dynamic unit testing for the specified page class.
 *
 * @coversNothing
 */
abstract class BaseHydePageUnitTest extends TestCase
{
    /**
     * @var class-string<\Hyde\Pages\Concerns\HydePage>|HydePage
     */
    protected static string|HydePage $page = HydePage::class;

    protected array $expectations;

    public function addExpectation(string $method, mixed $value): void
    {
        $this->expectations[$method] = $value;
    }

    protected function getExpectationValue(string $method): mixed
    {
        return $this->expectations[$method] ?? throw new Exception("No expectation set for method '$method'");
    }

    protected function expect(string $method): PendingExpectation
    {
        return new PendingExpectation($this, $method);
    }

    public function testPath()
    {
        $this->assertSame(
            $this->getExpectationValue('path'),
            static::$page::path(),
        );
    }

    public function testGetBladeView()
    {
        //
    }

    public function testSourcePath()
    {
        //
    }

    public function testFiles()
    {
        //
    }

    public function testNavigationMenuLabel()
    {
        //
    }

    public function testGetOutputPath()
    {
        //
    }

    public function testGet()
    {
        //
    }

    public function testOutputDirectory()
    {
        //
    }

    public function testParse()
    {
        //
    }

    public function testNavigationMenuGroup()
    {
        //
    }

    public function testNavigationMenuPriority()
    {
        //
    }

    public function testGetRouteKey()
    {
        //
    }

    public function testHtmlTitle()
    {
        //
    }

    public function testAll()
    {
        //
    }

    public function testMetadata()
    {
        //
    }

    public function test__construct()
    {
        //
    }

    public function testMake()
    {
        //
    }

    public function testGetRoute()
    {
        //
    }

    public function testShowInNavigation()
    {
        //
    }

    public function testGetSourcePath()
    {
        //
    }

    public function testGetLink()
    {
        //
    }

    public function testGetIdentifier()
    {
        //
    }

    public function testHas()
    {
        //
    }

    public function testToCoreDataObject()
    {
        //
    }

    public function testConstructFactoryData()
    {
        //
    }

    public function testFileExtension()
    {
        //
    }

    public function testSourceDirectory()
    {
        //
    }

    public function testCompile()
    {
        //
    }

    public function testMatter()
    {
        //
    }

    public function testOutputPath()
    {
        //
    }
}

class PendingExpectation
{
    protected BaseHydePageUnitTest $test;
    protected string $property;

    public function __construct(BaseHydePageUnitTest $test, string $property)
    {
        $this->test = $test;
        $this->property = $property;
    }

    public function toReturn($expected): void
    {
        $this->test->addExpectation($this->property, $expected);
    }
}
