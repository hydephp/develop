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

    protected function testMethod(string $method): void
    {
        $this->assertSame(
            $this->getExpectationValue($method),
            static::$page::$method(),
        );
    }

    public function testPath()
    {
        $this->testMethod('path');
    }

    public function testGetBladeView()
    {
        $this->testMethod('getBladeView');
    }

    public function testSourcePath()
    {
        $this->testMethod('sourcePath');
    }

    public function testFiles()
    {
        $this->testMethod('files');
    }

    public function testNavigationMenuLabel()
    {
        $this->testMethod('navigationMenuLabel');
    }

    public function testGetOutputPath()
    {
        $this->testMethod('getOutputPath');
    }

    public function testGet()
    {
        $this->testMethod('get');
    }

    public function testOutputDirectory()
    {
        $this->testMethod('outputDirectory');
    }

    public function testParse()
    {
        $this->testMethod('parse');
    }

    public function testNavigationMenuGroup()
    {
        $this->testMethod('navigationMenuGroup');
    }

    public function testNavigationMenuPriority()
    {
        $this->testMethod('navigationMenuPriority');
    }

    public function testGetRouteKey()
    {
        $this->testMethod('getRouteKey');
    }

    public function testHtmlTitle()
    {
        $this->testMethod('htmlTitle');
    }

    public function testAll()
    {
        $this->testMethod('all');
    }

    public function testMetadata()
    {
        $this->testMethod('metadata');
    }

    public function test__construct()
    {
        $this->testMethod('__construct');
    }

    public function testMake()
    {
        $this->testMethod('make');
    }

    public function testGetRoute()
    {
        $this->testMethod('getRoute');
    }

    public function testShowInNavigation()
    {
        $this->testMethod('showInNavigation');
    }

    public function testGetSourcePath()
    {
        $this->testMethod('getSourcePath');
    }

    public function testGetLink()
    {
        $this->testMethod('getLink');
    }

    public function testGetIdentifier()
    {
        $this->testMethod('getIdentifier');
    }

    public function testHas()
    {
        $this->testMethod('has');
    }

    public function testToCoreDataObject()
    {
        $this->testMethod('toCoreDataObject');
    }

    public function testConstructFactoryData()
    {
        $this->testMethod('constructFactoryData');
    }

    public function testFileExtension()
    {
        $this->testMethod('fileExtension');
    }

    public function testSourceDirectory()
    {
        $this->testMethod('sourceDirectory');
    }

    public function testCompile()
    {
        $this->testMethod('compile');
    }

    public function testMatter()
    {
        $this->testMethod('matter');
    }

    public function testOutputPath()
    {
        $this->testMethod('outputPath');
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
