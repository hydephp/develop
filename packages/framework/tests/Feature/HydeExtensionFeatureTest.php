<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use BadMethodCallException;
use Hyde\Foundation\Concerns\HydeExtension;
use Hyde\Foundation\Facades\Files;
use Hyde\Foundation\Facades\Pages;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\HydeCoreExtension;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Filesystem\SourceFile;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;
use InvalidArgumentException;
use stdClass;
use function app;
use function func_get_args;

/**
 * @covers \Hyde\Foundation\Concerns\HydeExtension
 * @covers \Hyde\Foundation\Concerns\ManagesExtensions
 * @covers \Hyde\Foundation\HydeKernel
 * @covers \Hyde\Foundation\Kernel\FileCollection
 * @covers \Hyde\Foundation\Kernel\PageCollection
 * @covers \Hyde\Foundation\Kernel\RouteCollection
 */
class HydeExtensionFeatureTest extends TestCase
{
    protected HydeKernel $kernel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel = HydeKernel::getInstance();
    }

    public function testBaseClassGetPageClasses()
    {
        $this->assertSame([], HydeExtension::getPageClasses());
    }

    public function testBaseClassDiscoveryHandlers()
    {
        HydeExtension::discoverFiles(Hyde::files());
        HydeExtension::discoverPages(Hyde::pages());
        HydeExtension::discoverRoutes(Hyde::routes());

        $this->markTestSuccessful();
    }

    public function testCanRegisterNewExtension()
    {
        HydeKernel::setInstance(new HydeKernel());

        $this->kernel = HydeKernel::getInstance();
        $this->kernel->registerExtension(HydeTestExtension::class);

        $this->assertSame([HydeCoreExtension::class, HydeTestExtension::class], $this->kernel->getRegisteredExtensions());
    }

    public function testHandlerMethodsAreCalledByDiscovery()
    {
        $this->kernel->registerExtension(HydeTestExtension::class);

        $this->assertSame([], HydeTestExtension::$callCache);

        $this->kernel->boot();

        $this->assertSame(['files', 'pages', 'routes'], HydeTestExtension::$callCache);

        HydeTestExtension::$callCache = [];
    }

    public function testFileHandlerDependencyInjection()
    {
        $this->kernel->registerExtension(InspectableTestExtension::class);
        $this->kernel->boot();

        $this->assertInstanceOf(FileCollection::class, ...InspectableTestExtension::getCalled('files'));
    }

    public function testPageHandlerDependencyInjection()
    {
        $this->kernel->registerExtension(InspectableTestExtension::class);
        $this->kernel->boot();

        $this->assertInstanceOf(PageCollection::class, ...InspectableTestExtension::getCalled('pages'));
    }

    public function testRouteHandlerDependencyInjection()
    {
        $this->kernel->registerExtension(InspectableTestExtension::class);
        $this->kernel->boot();

        $this->assertInstanceOf(RouteCollection::class, ...InspectableTestExtension::getCalled('routes'));
    }

    public function test_register_extension_method_throws_exception_when_kernel_is_already_booted()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot register an extension after the Kernel has been booted.');

        app(HydeKernel::class)->boot();
        app(HydeKernel::class)->registerExtension(HydeTestExtension::class);
    }

    public function test_register_extension_method_only_accepts_instances_of_hyde_extension()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The specified class must extend the HydeExtension class.');

        app(HydeKernel::class)->registerExtension(stdClass::class);
    }

    public function test_get_registered_page_classes_returns_core_extension_classes()
    {
        $this->assertSame(HydeCoreExtension::getPageClasses(), $this->kernel->getRegisteredPageClasses());
    }

    public function test_get_registered_page_classes_merges_all_extension_classes()
    {
        $this->kernel->registerExtension(HydeTestExtension::class);

        $this->assertSame(
            array_merge(HydeCoreExtension::getPageClasses(), HydeTestExtension::getPageClasses()),
            $this->kernel->getRegisteredPageClasses()
        );
    }

    public function test_merged_registered_page_classes_array_contents()
    {
        $this->assertSame([
            HtmlPage::class,
            BladePage::class,
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
        ], $this->kernel->getRegisteredPageClasses());

        $this->kernel->registerExtension(HydeTestExtension::class);

        $this->assertSame([
            HtmlPage::class,
            BladePage::class,
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
            HydeExtensionTestPage::class,
        ], $this->kernel->getRegisteredPageClasses());
    }

    public function test_register_extension_method_does_not_register_already_registered_classes()
    {
        $this->kernel->registerExtension(HydeTestExtension::class);
        $this->kernel->registerExtension(HydeTestExtension::class);

        $this->assertSame([HydeCoreExtension::class, HydeTestExtension::class], $this->kernel->getRegisteredExtensions());
    }

    public function test_custom_registered_pages_are_discovered_by_the_file_collection_class()
    {
        app(HydeKernel::class)->registerExtension(TestPageExtension::class);
        FileCollection::init(app(HydeKernel::class))->boot();

        $this->directory('foo');
        $this->file('foo/bar.txt');

        $this->assertArrayHasKey('foo/bar.txt', Files::all());
        $this->assertEquals(new SourceFile('foo/bar.txt', TestPageClass::class), Files::get('foo/bar.txt'));
    }

    public function test_custom_registered_pages_are_discovered_by_the_page_collection_class()
    {
        $this->directory('foo');
        $this->file('foo/bar.txt');

        app(HydeKernel::class)->registerExtension(TestPageExtension::class);
        PageCollection::init(app(HydeKernel::class))->boot();

        $this->assertArrayHasKey('foo/bar.txt', Pages::all());
        $this->assertEquals(new TestPageClass('bar'), Pages::get('foo/bar.txt'));
    }

    public function test_custom_registered_pages_are_discovered_by_the_route_collection_class()
    {
        $this->directory('foo');
        $this->file('foo/bar.txt');

        app(HydeKernel::class)->registerExtension(TestPageExtension::class);
        RouteCollection::init(app(HydeKernel::class))->boot();

        $this->assertArrayHasKey('foo/bar', Routes::all());
        $this->assertEquals(new Route(new TestPageClass('bar')), Routes::get('foo/bar'));
    }

    protected function markTestSuccessful(): void
    {
        $this->assertTrue(true);
    }
}

class HydeTestExtension extends HydeExtension
{
    // An easy way to assert the handlers are called.
    public static array $callCache = [];

    public static function getPageClasses(): array
    {
        return [
            HydeExtensionTestPage::class,
        ];
    }

    public static function discoverFiles(FileCollection $collection): void
    {
        static::$callCache[] = 'files';
    }

    public static function discoverPages(PageCollection $collection): void
    {
        static::$callCache[] = 'pages';
    }

    public static function discoverRoutes(RouteCollection $collection): void
    {
        static::$callCache[] = 'routes';
    }
}

class InspectableTestExtension extends HydeExtension
{
    private static array $callCache = [];

    public static function discoverFiles(FileCollection $collection): void
    {
        self::$callCache['files'] = func_get_args();
    }

    public static function discoverPages(PageCollection $collection): void
    {
        self::$callCache['pages'] = func_get_args();
    }

    public static function discoverRoutes(RouteCollection $collection): void
    {
        self::$callCache['routes'] = func_get_args();
    }

    public static function getCalled(string $method): array
    {
        return self::$callCache[$method];
    }
}

class HydeExtensionTestPage extends HydePage
{
    public static string $sourceDirectory = 'foo';
    public static string $outputDirectory = 'foo';
    public static string $fileExtension = '.txt';

    public function compile(): string
    {
        return '';
    }
}

class TestPageClass extends HydePage
{
    public static string $sourceDirectory = 'foo';
    public static string $outputDirectory = 'foo';
    public static string $fileExtension = '.txt';

    public function compile(): string
    {
        return '';
    }
}

class TestPageExtension extends HydeExtension
{
    public static function getPageClasses(): array
    {
        return [
            TestPageClass::class,
        ];
    }
}
