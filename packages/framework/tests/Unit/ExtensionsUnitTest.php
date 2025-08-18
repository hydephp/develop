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
use BadMethodCallException;
use stdClass;

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
        $this->kernel->registerExtension(HydeTestExtension::class);

        $this->assertSame([HydeCoreExtension::class, HydeTestExtension::class], $this->kernel->getRegisteredExtensions());
    }

    public function testRegisterExtensionAfterKernelIsBooted()
    {
        $this->kernel->boot();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot register an extension after the Kernel has been booted.');

        $this->kernel->registerExtension(HydeTestExtension::class);
    }

    public function testRegisterExtensionWithInvalidExtensionClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Extension [stdClass] must extend the HydeExtension class.');

        $this->kernel->registerExtension(stdClass::class);
    }

    public function testRegisterExtensionWithNonClassString()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Extension [foo] must extend the HydeExtension class.');

        $this->kernel->registerExtension('foo');
    }

    public function testRegisterExtensionWithAlreadyRegisteredExtension()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Extension ['.HydeTestExtension::class.'] is already registered.');

        $this->kernel->registerExtension(HydeTestExtension::class);
        $this->kernel->registerExtension(HydeTestExtension::class);
    }

    public function testRegisterExtensionMethodDoesNotRegisterAlreadyRegisteredClasses()
    {
        $this->kernel->registerExtension(HydeTestExtension::class);

        try {
            $this->kernel->registerExtension(HydeTestExtension::class);
        } catch (InvalidArgumentException) {
            //
        }

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

    public function testGetExtensionGenerics()
    {
        $this->kernel->registerExtension(PolymorphicTestExtension::class);
        $extension = $this->kernel->getExtension(PolymorphicTestExtension::class);

        $this->assertInstanceOf(PolymorphicTestExtension::class, $extension);
        $this->assertInstanceOf(HydeExtension::class, $extension);

        // We can't test generics in PHPUnit, but we can programmatically verify the method is called, and visually verify IDE support.
        $this->assertSame('foo', $extension->example());
    }

    public function testHasExtensionWithValidExtension()
    {
        $this->assertTrue($this->kernel->hasExtension(HydeCoreExtension::class));
    }

    public function testHasExtensionWithCustomExtension()
    {
        $this->kernel->registerExtension(HydeTestExtension::class);

        $this->assertTrue($this->kernel->hasExtension(HydeTestExtension::class));
    }

    public function testHasExtensionWithInvalidExtension()
    {
        $this->assertFalse($this->kernel->hasExtension('foo'));
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

    public function testGetRegisteredPageClassesReturnsCoreExtensionClasses()
    {
        $this->assertSame(HydeCoreExtension::getPageClasses(), $this->kernel->getRegisteredPageClasses());
    }

    public function testGetRegisteredPageClassesMergesAllExtensionClasses()
    {
        $this->kernel->registerExtension(HydeTestExtension::class);

        $this->assertSame(
            array_merge(HydeCoreExtension::getPageClasses(), HydeTestExtension::getPageClasses()),
            $this->kernel->getRegisteredPageClasses()
        );
    }

    public function testMergedRegisteredPageClassesArrayContents()
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

class PolymorphicTestExtension extends HydeExtension
{
    public function example(): string
    {
        return 'foo';
    }
}
