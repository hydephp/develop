<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\BladePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\DocumentationPage;
use Hyde\Foundation\Concerns\HydeExtension;
use Hyde\Foundation\HydeCoreExtension;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Foundation\HydeKernel
 * @covers \Hyde\Foundation\Concerns\HydeExtension
 * @covers \Hyde\Foundation\Concerns\ManagesExtensions
 *
 * @see \Hyde\Framework\Testing\Feature\HydeKernelTest
 * @see \Hyde\Framework\Testing\Feature\HydeExtensionFeatureTest
 */
class ExtensionsUnitTest extends UnitTestCase
{
    protected HydeKernel $kernel;

    public function setUp(): void
    {
        self::setupKernel();
        self::mockConfig();

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
