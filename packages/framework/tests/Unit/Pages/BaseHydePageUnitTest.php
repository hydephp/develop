<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Exception;
use Hyde\Hyde;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Testing\TestCase;

/**
 * This extendable base text class provides dynamic unit testing for the specified page class.
 *
 * These unit tests ensure all inherited methods are callable, and that they return the expected value.
 *
 * @coversNothing
 */
abstract class BaseHydePageUnitTest extends TestCase implements BaseHydePageUnitTestMethods
{
    /**
     * @var class-string<\Hyde\Pages\Concerns\HydePage>|HydePage
     */
    protected static string|HydePage $page = HydePage::class;

    protected function testMethod(string $method, mixed $returns, array $constructorParameters = [], array $methodParameters = []): void
    {
        $this->assertSame(
            $returns,
            $this->page($constructorParameters)->$method(...$methodParameters),
        );
    }

    protected function page(array $parameters = []): HydePage
    {
        return app()->make(static::$page, $parameters);
    }

    /** @see HydePage::Path */
    public function testPath()
    {
        $this->testMethod('path', Hyde::getBasePath() . DIRECTORY_SEPARATOR . static::$page::$sourceDirectory);
    }

    /** @see HydePage::GetBladeView */
    public function testGetBladeView()
    {
        $this->testMethod('getBladeView', static::$page::$template ?? '');
    }

    /** @see HydePage::SourcePath */
    public function testSourcePath()
    {
        $this->testMethod('sourcePath', static::$page::$sourceDirectory.'/foo'.static::$page::$fileExtension, [], ['foo']);
    }

    public function testFiles()
    {
        $this->testMethod('files', 'foo');
    }

    public function testNavigationMenuLabel()
    {
        $this->testMethod('navigationMenuLabel', 'foo');
    }

    public function testGetOutputPath()
    {
        $this->testMethod('getOutputPath', 'foo');
    }

    public function testGet()
    {
        $this->testMethod('get', 'foo');
    }

    public function testOutputDirectory()
    {
        $this->testMethod('outputDirectory', 'foo');
    }

    public function testParse()
    {
        $this->testMethod('parse', 'foo');
    }

    public function testNavigationMenuGroup()
    {
        $this->testMethod('navigationMenuGroup', 'foo');
    }

    public function testNavigationMenuPriority()
    {
        $this->testMethod('navigationMenuPriority', 'foo');
    }

    public function testGetRouteKey()
    {
        $this->testMethod('getRouteKey', 'foo');
    }

    public function testHtmlTitle()
    {
        $this->testMethod('htmlTitle', 'foo');
    }

    public function testAll()
    {
        $this->testMethod('all', 'foo');
    }

    public function testMetadata()
    {
        $this->testMethod('metadata', 'foo');
    }

    public function test__construct()
    {
        $this->testMethod('__construct', 'foo');
    }

    public function testMake()
    {
        $this->testMethod('make', 'foo');
    }

    public function testGetRoute()
    {
        $this->testMethod('getRoute', 'foo');
    }

    public function testShowInNavigation()
    {
        $this->testMethod('showInNavigation', 'foo');
    }

    public function testGetSourcePath()
    {
        $this->testMethod('getSourcePath', 'foo');
    }

    public function testGetLink()
    {
        $this->testMethod('getLink', 'foo');
    }

    public function testGetIdentifier()
    {
        $this->testMethod('getIdentifier', 'foo');
    }

    public function testHas()
    {
        $this->testMethod('has', 'foo');
    }

    public function testToCoreDataObject()
    {
        $this->testMethod('toCoreDataObject', 'foo');
    }

    public function testConstructFactoryData()
    {
        $this->testMethod('constructFactoryData', 'foo');
    }

    public function testFileExtension()
    {
        $this->testMethod('fileExtension', 'foo');
    }

    public function testSourceDirectory()
    {
        $this->testMethod('sourceDirectory', 'foo');
    }

    public function testCompile()
    {
        $this->testMethod('compile', 'foo');
    }

    public function testMatter()
    {
        $this->testMethod('matter', 'foo');
    }

    public function testOutputPath()
    {
        $this->testMethod('outputPath', 'foo');
    }
}

interface BaseHydePageUnitTestMethods
{
    public function testPath();
    public function testGetBladeView();
    public function testSourcePath();
    public function testFiles();
    public function testNavigationMenuLabel();
    public function testGetOutputPath();
    public function testGet();
    public function testOutputDirectory();
    public function testParse();
    public function testNavigationMenuGroup();
    public function testNavigationMenuPriority();
    public function testGetRouteKey();
    public function testHtmlTitle();
    public function testAll();
    public function testMetadata();
    public function test__construct();
    public function testMake();
    public function testGetRoute();
    public function testShowInNavigation();
    public function testGetSourcePath();
    public function testGetLink();
    public function testGetIdentifier();
    public function testHas();
    public function testToCoreDataObject();
    public function testConstructFactoryData();
    public function testFileExtension();
    public function testSourceDirectory();
    public function testCompile();
    public function testMatter();
    public function testOutputPath();
}
