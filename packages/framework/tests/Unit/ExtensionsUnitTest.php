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
use InvalidArgumentException;

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
        $extension = new InstantiableHydeExtension();

        $extension->discoverFiles(Hyde::files());
        $extension->discoverPages(Hyde::pages());
        $extension->discoverRoutes(Hyde::routes());

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

        InspectableTestExtension::setTest($this);

        FileCollection::init($this->kernel)->boot();
    }

    public function testPageHandlerDependencyInjection()
    {
        $this->kernel->registerExtension(InspectableTestExtension::class);

        InspectableTestExtension::setTest($this);

        PageCollection::init($this->kernel)->boot();
    }

    public function testRouteHandlerDependencyInjection()
    {
        $this->kernel->registerExtension(InspectableTestExtension::class);

        InspectableTestExtension::setTest($this);

        RouteCollection::init($this->kernel)->boot();
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

    public function testGetExtensionWithValidExtension()
    {
        $this->assertInstanceOf(HydeCoreExtension::class, $this->kernel->getExtension(HydeCoreExtension::class));
    }

    public function testGetExtensionWithCustomExtension()
    {
        $this->kernel->registerExtension(HydeTestExtension::class);

        $this->assertInstanceOf(HydeTestExtension::class, $this->kernel->getExtension(HydeTestExtension::class));
    }

    public function testGetExtensionWithInvalidExtension()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Extension [foo] is not registered.');

        $this->kernel->getExtension('foo');
    }

    protected function markTestSuccessful(): void
    {
        $this->assertTrue(true);
    }
}

class InstantiableHydeExtension extends HydeExtension
{
    //
}

class HydeTestExtension extends HydeExtension
{
    public static function getPageClasses(): array
    {
        return [
            HydeExtensionTestPage::class,
        ];
    }
}

class InspectableTestExtension extends HydeExtension
{
    private static UnitTestCase $test;

    public static function setTest(UnitTestCase $test): void
    {
        self::$test = $test;
    }

    public function discoverFiles($collection): void
    {
        self::$test->assertInstanceOf(FileCollection::class, $collection);
    }

    public function discoverPages($collection): void
    {
        self::$test->assertInstanceOf(PageCollection::class, $collection);
    }

    public function discoverRoutes($collection): void
    {
        self::$test->assertInstanceOf(RouteCollection::class, $collection);
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
