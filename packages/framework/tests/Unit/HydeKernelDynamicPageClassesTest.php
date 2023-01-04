<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\Facades;
use Hyde\Foundation\FileCollection;
use Hyde\Hyde;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Filesystem\SourceFile;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\HydeKernel
 * @covers \Hyde\Foundation\Concerns\ManagesHydeKernel
 */
class HydeKernelDynamicPageClassesTest extends TestCase
{
    public function test_get_registered_page_classes_method()
    {
        $this->assertSame([], Hyde::getRegisteredPageClasses());
    }

    public function test_register_page_class_method_adds_specified_class_name_to_index()
    {
        Hyde::registerPageClass(TestPageClass::class);
        $this->assertSame([TestPageClass::class], Hyde::getRegisteredPageClasses());
    }

    public function test_register_page_class_method_does_not_add_already_added_class_names()
    {
        Hyde::registerPageClass(TestPageClass::class);
        Hyde::registerPageClass(TestPageClass::class);
        $this->assertSame([TestPageClass::class], Hyde::getRegisteredPageClasses());
    }

    public function test_register_page_class_method_only_accepts_instances_of_hyde_page_class()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The specified class must be a subclass of HydePage.');
        Hyde::registerPageClass(\stdClass::class);
    }

    public function test_register_page_class_method_throws_exception_when_collection_is_already_booted()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot register a page class after the Kernel has been booted.');

        Hyde::boot();
        Hyde::registerPageClass(TestPageClass::class);
    }

    public function test_custom_registered_pages_are_discovered()
    {
        $this->directory('foo');
        $this->file('foo/bar.txt');
        Hyde::registerPageClass(TestPageClassWithSourceInformation::class);

        $this->assertArrayHasKey('foo/bar.txt', Facades\FileCollection::all());
        $this->assertEquals(new SourceFile('foo/bar.txt', TestPageClassWithSourceInformation::class), Facades\FileCollection::get('foo/bar.txt'));
    }

    public function test_custom_registered_pages_can_be_further_processed_and_parsed()
    {
        $this->markTestSkipped('Todo');
    }
}

abstract class TestPageClass extends HydePage
{
    //
}

class TestPageClassWithSourceInformation extends HydePage
{
    public static string $sourceDirectory = 'foo';
    public static string $fileExtension = '.txt';

    public function compile(): string
    {
        return '';
    }
}
