<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Testing\Concerns\Internal\HandlesPageFilesystemTestClass;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Concerns\HydePage
 */
class HandlesPageFilesystemTest extends TestCase
{
    public function testSourceDirectory()
    {
        $this->assertSame(
            'source',
            HandlesPageFilesystemTestClass::sourceDirectory()
        );
    }

    public function testOutputDirectory()
    {
        $this->assertSame(
            'output',
            HandlesPageFilesystemTestClass::outputDirectory()
        );
    }

    public function testFileExtension()
    {
        $this->assertSame(
            '.md',
            HandlesPageFilesystemTestClass::fileExtension()
        );
    }

    public function testSourcePath()
    {
        $this->assertSame(
            'source/hello-world.md',
            HandlesPageFilesystemTestClass::sourcePath('hello-world')
        );
    }

    public function testOutputPath()
    {
        $this->assertSame(
            'output/hello-world.html',
            HandlesPageFilesystemTestClass::outputPath('hello-world')
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
}