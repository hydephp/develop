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
            'packages/framework/resources/views/layouts/app.blade.php' => 'resources/views/vendor/hyde/layouts/app.blade.php',
            'packages/framework/resources/views/layouts/page.blade.php' => 'resources/views/vendor/hyde/layouts/page.blade.php',
            'packages/framework/resources/views/layouts/post.blade.php' => 'resources/views/vendor/hyde/layouts/post.blade.php',
        ]);

        $this->assertSame([
            'packages/framework/resources/views/layouts/app.blade.php' => 'app.blade.php',
            'packages/framework/resources/views/layouts/page.blade.php' => 'page.blade.php',
            'packages/framework/resources/views/layouts/post.blade.php' => 'post.blade.php',
        ], $helper->getFileChoices());
    }

    public function testOnlyFiltersPublishableFiles(): void
    {
        $helper = new InteractivePublishCommandHelper([
            'packages/framework/resources/views/layouts/app.blade.php' => 'resources/views/vendor/hyde/layouts/app.blade.php',
            'packages/framework/resources/views/layouts/page.blade.php' => 'resources/views/vendor/hyde/layouts/page.blade.php',
            'packages/framework/resources/views/layouts/post.blade.php' => 'resources/views/vendor/hyde/layouts/post.blade.php',
        ]);

        $helper->only([
            'packages/framework/resources/views/layouts/app.blade.php',
            'packages/framework/resources/views/layouts/post.blade.php'
        ]);

        $this->assertSame([
            'packages/framework/resources/views/layouts/app.blade.php' => 'app.blade.php',
            'packages/framework/resources/views/layouts/post.blade.php' => 'post.blade.php',
        ], $helper->getFileChoices());
    }

    public function testPublishFiles(): void
    {
        $this->filesystem->shouldReceive('ensureDirectoryExists')->times(3);
        $this->filesystem->shouldReceive('copy')->times(3);

        $helper = new InteractivePublishCommandHelper([
            'packages/framework/resources/views/layouts/app.blade.php' => 'resources/views/vendor/hyde/layouts/app.blade.php',
            'packages/framework/resources/views/layouts/page.blade.php' => 'resources/views/vendor/hyde/layouts/page.blade.php',
            'packages/framework/resources/views/layouts/post.blade.php' => 'resources/views/vendor/hyde/layouts/post.blade.php',
        ]);

        $helper->publishFiles();

        $this->filesystem->shouldHaveReceived('ensureDirectoryExists')->with(Hyde::path('resources/views/vendor/hyde/layouts'))->times(3);

        $this->filesystem->shouldHaveReceived('copy')->with(
            Hyde::path('packages/framework/resources/views/layouts/app.blade.php'),
            Hyde::path('resources/views/vendor/hyde/layouts/app.blade.php')
        )->once();

        $this->filesystem->shouldHaveReceived('copy')->with(
            Hyde::path('packages/framework/resources/views/layouts/page.blade.php'),
            Hyde::path('resources/views/vendor/hyde/layouts/page.blade.php')
        )->once();

        $this->filesystem->shouldHaveReceived('copy')->with(
            Hyde::path('packages/framework/resources/views/layouts/post.blade.php'),
            Hyde::path('resources/views/vendor/hyde/layouts/post.blade.php')
        )->once();
    }

    public function testFormatOutputForSingleFile(): void
    {
        $helper = new InteractivePublishCommandHelper([
            'packages/framework/resources/views/layouts/app.blade.php' => 'resources/views/vendor/hyde/layouts/app.blade.php',
        ]);

        $this->assertSame(
            'Published file to [resources/views/vendor/hyde/layouts/app.blade.php].',
            $helper->formatOutput()
        );
    }

    public function testFormatOutputForMultipleFiles(): void
    {
        $helper = new InteractivePublishCommandHelper([
            'packages/framework/resources/views/layouts/app.blade.php' => 'resources/views/vendor/hyde/layouts/app.blade.php',
            'packages/framework/resources/views/layouts/page.blade.php' => 'resources/views/vendor/hyde/layouts/page.blade.php',
        ]);

        $this->assertSame(
            'Published all files to [resources/views/vendor/hyde/layouts].',
            $helper->formatOutput()
        );
    }
}
