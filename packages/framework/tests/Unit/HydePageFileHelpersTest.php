<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Concerns\HydePage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Concerns\HydePage
 */
class HydePageFileHelpersTest extends TestCase
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
