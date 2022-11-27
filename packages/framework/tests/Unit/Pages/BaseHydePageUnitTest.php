<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Exception;
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
