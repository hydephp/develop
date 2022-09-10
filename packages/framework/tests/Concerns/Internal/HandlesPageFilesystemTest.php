<?php

namespace Hyde\Framework\Testing\Concerns\Internal;

use Hyde\Framework\Concerns\HydePage;
use Hyde\Framework\Concerns\Internal\HandlesPageFilesystem;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Concerns\HydePage
 * @covers \Hyde\Framework\Concerns\Internal\HandlesPageFilesystem
 */
class HandlesPageFilesystemTest extends TestCase
{
    public function testGetSourceDirectory()
    {
        $this->assertSame(
            'source',
            HandlesPageFilesystemTestClass::sourceDirectory()
        );
    }

    public function testGetOutputDirectory()
    {
        $this->assertSame(
            'output',
            HandlesPageFilesystemTestClass::outputDirectory()
        );
    }

    public function testGetFileExtension()
    {
        $this->assertSame(
            '.md',
            HandlesPageFilesystemTestClass::getFileExtension()
        );
    }

    public function testQualifyBasename()
    {
        $this->assertSame(
            'source/hello-world.md',
            HandlesPageFilesystemTestClass::qualifyBasename('hello-world')
        );
    }

    public function testGetOutputLocation()
    {
        $this->assertSame(
            'output/hello-world.html',
            HandlesPageFilesystemTestClass::getOutputLocation('hello-world')
        );
    }

    public function testGetSourcePath()
    {
        $this->assertSame(
            'source/hello-world.md',
            (new HandlesPageFilesystemTestClass('hello-world'))->getSourcePath()
        );
    }

    public function testGetOutputPath()
    {
        $this->assertSame(
            'output/hello-world.html',
            (new HandlesPageFilesystemTestClass('hello-world'))->getOutputPath()
        );
    }

    public function testAllMethodsAreCovered()
    {
        $this->assertCount(
            (count(array_filter(
                get_class_methods($this),
                fn ($method) => str_starts_with($method, 'test')
            )) - 1),
            get_class_methods(HandlesPageFilesystem::class),
        );
    }
}

class HandlesPageFilesystemTestClass extends HydePage
{
    public static string $sourceDirectory = 'source';
    public static string $outputDirectory = 'output';
    public static string $fileExtension = '.md';
    public static string $template = 'template';

    public function compile(): string
    {
        return '';
    }
}
