<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Console\Helpers\InteractivePublishCommandHelper;
use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Mockery;

/**
 * @covers \Hyde\Console\Helpers\InteractivePublishCommandHelper
 */
class InteractivePublishCommandHelperTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected Filesystem|Mockery\MockInterface $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = $this->mockFilesystemStrict();
        File::swap($this->filesystem);
    }

    protected function tearDown(): void
    {
        $this->verifyMockeryExpectations();
        File::swap(null);
    }

    public function testGetFileChoices(): void
    {
        $helper = new InteractivePublishCommandHelper([
            'source/path/file1.php' => 'target/path/file1.php',
            'source/path/file2.php' => 'target/path/file2.php',
        ]);

        $this->assertSame([
            'source/path/file1.php' => 'file1.php',
            'source/path/file2.php' => 'file2.php',
        ], $helper->getFileChoices());
    }

    public function testOnlyFiltersPublishableFiles(): void
    {
        $helper = new InteractivePublishCommandHelper([
            'source/path/file1.php' => 'target/path/file1.php',
            'source/path/file2.php' => 'target/path/file2.php',
            'source/path/file3.php' => 'target/path/file3.php',
        ]);

        $helper->only(['source/path/file1.php', 'source/path/file3.php']);

        $this->assertSame([
            'source/path/file1.php' => 'file1.php',
            'source/path/file3.php' => 'file3.php',
        ], $helper->getFileChoices());
    }

    public function testPublishFiles(): void
    {
        $this->filesystem->shouldReceive('ensureDirectoryExists')->twice();
        $this->filesystem->shouldReceive('copy')->twice();

        $helper = new InteractivePublishCommandHelper([
            'source/path/file1.php' => 'target/path/file1.php',
            'source/path/file2.php' => 'target/path/file2.php',
        ]);

        $helper->publishFiles();

        $this->filesystem->shouldHaveReceived('ensureDirectoryExists')->with(Hyde::path('target/path'))->twice();
        $this->filesystem->shouldHaveReceived('copy')->with(Hyde::path('source/path/file1.php'), Hyde::path('target/path/file1.php'))->once();
        $this->filesystem->shouldHaveReceived('copy')->with(Hyde::path('source/path/file2.php'), Hyde::path('target/path/file2.php'))->once();
    }

    public function testFormatOutputForSingleFile(): void
    {
        $helper = new InteractivePublishCommandHelper([
            'source/path/file1.php' => 'target/path/file1.php',
        ]);

        $this->assertSame(
            'Published file to [target/path/file1.php].',
            $helper->formatOutput()
        );
    }

    public function testFormatOutputForMultipleFiles(): void
    {
        $helper = new InteractivePublishCommandHelper([
            'source/path/file1.php' => 'target/path/file1.php',
            'source/path/file2.php' => 'target/path/file2.php',
        ]);

        $this->assertSame(
            'Published all files to [target/path].',
            $helper->formatOutput()
        );
    }
}
