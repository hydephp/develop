<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

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
