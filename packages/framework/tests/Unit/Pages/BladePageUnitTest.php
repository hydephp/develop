<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Pages\BladePage;
use Hyde\Pages\Concerns\HydePage;

require_once __DIR__ . '/BaseHydePageUnitTest.php';

/**
 * @covers \Hyde\Pages\BladePage
 */
class BladePageUnitTest extends BaseHydePageUnitTest
{
    protected static string|HydePage $page = BladePage::class;

    public function testPath()
    {
        $this->testMethod('path', 'foo');
    }

    public function testGetBladeView()
    {
        $this->testMethod('getBladeView', 'foo');
    }

    public function testSourcePath()
    {
        $this->testMethod('sourcePath', 'foo');
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
