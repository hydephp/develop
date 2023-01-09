<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use function app;
use BadMethodCallException;
use Hyde\Foundation\Facades;
use Hyde\Foundation\HydeKernel;
use Hyde\Pages\BladePage;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\VirtualPage;
use Hyde\Support\Filesystem\SourceFile;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;
use InvalidArgumentException;
use stdClass;

/**
 * @covers \Hyde\Foundation\HydeKernel
 * @covers \Hyde\Foundation\Concerns\ManagesHydeKernel
 * @covers \Hyde\Foundation\FileCollection
 * @covers \Hyde\Foundation\PageCollection
 */
class HydeKernelDynamicPageClassesTest extends TestCase
{
    public function test_get_registered_page_classes_method()
    {
        $this->assertSame([
            HtmlPage::class,
            BladePage::class,
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
        ], app(HydeKernel::class)->getRegisteredPageClasses());
    }

    public function test_register_page_class_method_adds_specified_class_name_to_index()
    {
        app(HydeKernel::class)->registerPageClass(TestPageClass::class);
        $this->assertSame([
            HtmlPage::class,
            BladePage::class,
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
            TestPageClass::class,
        ], app(HydeKernel::class)->getRegisteredPageClasses());
    }

    public function test_register_page_class_method_does_not_add_already_added_class_names()
    {
        app(HydeKernel::class)->registerPageClass(TestPageClass::class);
        app(HydeKernel::class)->registerPageClass(TestPageClass::class);
        $this->assertSame([
            HtmlPage::class,
            BladePage::class,
            MarkdownPage::class,
            MarkdownPost::class,
            DocumentationPage::class,
            TestPageClass::class,
        ], app(HydeKernel::class)->getRegisteredPageClasses());
    }

    public function test_register_page_class_method_only_accepts_instances_of_hyde_page_class()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The specified class must be a subclass of HydePage.');
        app(HydeKernel::class)->registerPageClass(stdClass::class);
    }

    public function test_register_page_class_method_does_not_accept_classes_that_implement_dynamic_page_interface()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The specified class must not be a subclass of DynamicPage.');
        app(HydeKernel::class)->registerPageClass(VirtualPage::class);
    }

    public function test_register_page_class_method_throws_exception_when_collection_is_already_booted()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot register a page class after the Kernel has been booted.');

        app(HydeKernel::class)->boot();
        app(HydeKernel::class)->registerPageClass(TestPageClass::class);
    }

    // Test custom registered pages can be further processed and parsed

    public function test_custom_registered_pages_are_discovered_by_the_file_collection_class()
    {
        $this->directory('foo');
        $this->file('foo/bar.txt');
        app(HydeKernel::class)->registerPageClass(TestPageClassWithSourceInformation::class);

        $this->assertArrayHasKey('foo/bar.txt', Facades\FileCollection::all());
        $this->assertEquals(new SourceFile('foo/bar.txt', TestPageClassWithSourceInformation::class), Facades\FileCollection::get('foo/bar.txt'));
    }

    public function test_custom_registered_pages_are_discovered_by_the_page_collection_class()
    {
        $this->directory('foo');
        $this->file('foo/bar.txt');
        app(HydeKernel::class)->registerPageClass(TestPageClassWithSourceInformation::class);
        $this->assertArrayHasKey('foo/bar.txt', Facades\PageCollection::all());
        $this->assertEquals(new TestPageClassWithSourceInformation('bar'), Facades\PageCollection::get('foo/bar.txt'));
    }

    public function test_custom_registered_pages_are_discovered_by_the_route_collection_class()
    {
        $this->directory('foo');
        $this->file('foo/bar.txt');
        app(HydeKernel::class)->registerPageClass(TestPageClassWithSourceInformation::class);
        $this->assertArrayHasKey('foo/bar', Facades\Router::all());
        $this->assertEquals(new Route(new TestPageClassWithSourceInformation('bar')), Facades\Router::get('foo/bar'));
    }
}

abstract class TestPageClass extends HydePage
{
    //
}

class TestPageClassWithSourceInformation extends HydePage
{
    public static string $sourceDirectory = 'foo';
    public static string $outputDirectory = 'foo';
    public static string $fileExtension = '.txt';

    public function compile(): string
    {
        return '';
    }
}
