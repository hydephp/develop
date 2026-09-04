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
 * @see \Hyde\Framework\Testing\Feature\HydeKernelTest
 * @see \Hyde\Framework\Testing\Feature\HydeExtensionFeatureTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\HydeKernel::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\Concerns\HydeExtension::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Foundation\Concerns\ManagesExtensions::class)]
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

    public function testCanReplaceARegisteredPageClassWithASubclass()
    {
        $this->kernel->replacePageClass(MarkdownPost::class, ReplacementMarkdownPost::class);

        $this->assertSame(ReplacementMarkdownPost::class, $this->kernel->resolvePageClass(MarkdownPost::class));
        $this->assertContains(ReplacementMarkdownPost::class, $this->kernel->getRegisteredPageClasses());
        $this->assertNotContains(MarkdownPost::class, $this->kernel->getRegisteredPageClasses());
    }

    public function testCanRegisterAReplacementBeforeItsOriginalPageClassExtension()
    {
        $this->kernel->replacePageClass(ReplaceableExtensionPage::class, ReplacementExtensionPage::class);
        $this->kernel->registerExtension(ReplaceablePageExtension::class);

        $this->assertContains(ReplacementExtensionPage::class, $this->kernel->getRegisteredPageClasses());
        $this->assertNotContains(ReplaceableExtensionPage::class, $this->kernel->getRegisteredPageClasses());
    }

    public function testEffectivePageClassRegistryIsDeduplicated()
    {
        $this->kernel->replacePageClass(MarkdownPost::class, ReplacementMarkdownPost::class);
        $this->kernel->registerExtension(ReplacementPageExtension::class);

        $this->assertSame(1, array_count_values($this->kernel->getRegisteredPageClasses())[ReplacementMarkdownPost::class]);
    }

    public function testRegisteringTheSameReplacementTwiceIsIdempotent()
    {
        $this->kernel->replacePageClass(MarkdownPost::class, ReplacementMarkdownPost::class);
        $this->kernel->replacePageClass(MarkdownPost::class, ReplacementMarkdownPost::class);

        $this->assertSame(ReplacementMarkdownPost::class, $this->kernel->resolvePageClass(MarkdownPost::class));
    }

    public function testCannotReplaceAPageClassAfterTheKernelHasBooted()
    {
        $this->kernel->boot();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot replace a page class after Kernel booting has started.');

        $this->kernel->replacePageClass(MarkdownPost::class, ReplacementMarkdownPost::class);
    }

    public function testCannotReplaceAPageClassAfterKernelBootingHasStarted()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot replace a page class after Kernel booting has started.');

        $this->kernel->booting(function (HydeKernel $kernel): void {
            $kernel->replacePageClass(MarkdownPost::class, ReplacementMarkdownPost::class);
        });

        $this->kernel->boot();
    }

    public function testOriginalReplacementClassMustBeAHydePage()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Original page class [stdClass] must extend the HydePage class.');

        $this->kernel->replacePageClass(stdClass::class, ReplacementMarkdownPost::class);
    }

    public function testReplacementClassMustExtendTheOriginalPageClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Replacement page class ['.MarkdownPage::class.'] must extend ['.MarkdownPost::class.'].');

        $this->kernel->replacePageClass(MarkdownPost::class, MarkdownPage::class);
    }

    public function testCannotRegisterCompetingReplacements()
    {
        $this->kernel->replacePageClass(MarkdownPost::class, ReplacementMarkdownPost::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Page class ['.MarkdownPost::class.'] has already been replaced by ['.ReplacementMarkdownPost::class.'].');

        $this->kernel->replacePageClass(MarkdownPost::class, OtherReplacementMarkdownPost::class);
    }

    public function testCannotChainPageClassReplacements()
    {
        $this->kernel->replacePageClass(MarkdownPost::class, ReplacementMarkdownPost::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Page class replacements cannot be chained.');

        $this->kernel->replacePageClass(ReplacementMarkdownPost::class, NestedReplacementMarkdownPost::class);
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
    public static string $sourceExtension = '.txt';

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

class ReplacementMarkdownPost extends MarkdownPost
{
}

class OtherReplacementMarkdownPost extends MarkdownPost
{
}

class NestedReplacementMarkdownPost extends ReplacementMarkdownPost
{
}

class ReplaceableExtensionPage extends HydePage
{
    public static string $sourceDirectory = 'replaceable';
    public static string $outputDirectory = 'replaceable';
    public static string $sourceExtension = '.txt';

    public function compile(): string
    {
        return '';
    }
}

class ReplacementExtensionPage extends ReplaceableExtensionPage
{
}

class ReplaceablePageExtension extends HydeExtension
{
    public static function getPageClasses(): array
    {
        return [ReplaceableExtensionPage::class];
    }
}

class ReplacementPageExtension extends HydeExtension
{
    public static function getPageClasses(): array
    {
        return [ReplacementMarkdownPost::class];
    }
}
