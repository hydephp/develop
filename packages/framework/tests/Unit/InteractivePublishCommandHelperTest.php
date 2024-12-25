<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\CreatesApplication;
use Illuminate\Filesystem\Filesystem;
use Mockery;
use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;
use Hyde\Foundation\Providers\ViewServiceProvider;
use Hyde\Console\Helpers\InteractivePublishCommandHelper;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @covers \Hyde\Console\Helpers\InteractivePublishCommandHelper
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\PublishViewsCommandTest
 */
class InteractivePublishCommandHelperTest extends UnitTestCase
{
    use CreatesApplication;

    protected static bool $needsKernel = true;

    protected Filesystem|Mockery\MockInterface $filesystem;

    protected function setUp(): void
    {
        $app = $this->createApplication();

        $this->filesystem = $this->mockFilesystemStrict();
        $this->filesystem->shouldReceive('allFiles')->andReturn([])->byDefault();

        File::swap($this->filesystem);
        Blade::partialMock()->shouldReceive('component');

        (new ViewServiceProvider($app))->boot();
    }

    protected function tearDown(): void
    {
        $this->verifyMockeryExpectations();

        Facade::clearResolvedInstances();
    }

    public function testGetFileChoices()
    {
        $this->filesystem->shouldReceive('allFiles')->andReturn([
            new SplFileInfo(Hyde::path('packages/framework/resources/views/layouts/app.blade.php'), '', 'app.blade.php'),
            new SplFileInfo(Hyde::path('packages/framework/resources/views/layouts/page.blade.php'), '', 'page.blade.php'),
            new SplFileInfo(Hyde::path('packages/framework/resources/views/layouts/post.blade.php'), '', 'post.blade.php'),
        ]);

        $helper = new InteractivePublishCommandHelper('hyde-layouts');

        $this->assertSame([
            'resources/views/vendor/hyde/layouts/app.blade.php' => 'app.blade.php',
            'resources/views/vendor/hyde/layouts/page.blade.php' => 'page.blade.php',
            'resources/views/vendor/hyde/layouts/post.blade.php' => 'post.blade.php',
        ], $helper->getFileChoices());
    }

    public function testHandle()
    {
        $this->filesystem->shouldReceive('allFiles')->andReturn([
            new SplFileInfo(Hyde::path('packages/framework/resources/views/layouts/app.blade.php'), '', 'app.blade.php'),
            new SplFileInfo(Hyde::path('packages/framework/resources/views/layouts/page.blade.php'), '', 'page.blade.php'),
            new SplFileInfo(Hyde::path('packages/framework/resources/views/layouts/post.blade.php'), '', 'post.blade.php'),
        ]);

        $helper = new InteractivePublishCommandHelper('hyde-layouts');

        $this->filesystem->shouldReceive('ensureDirectoryExists')->twice();
        $this->filesystem->shouldReceive('copy')->twice();

        $helper->handle([
            'resources/views/vendor/hyde/layouts/app.blade.php',
            'resources/views/vendor/hyde/layouts/page.blade.php',
        ]);

        $this->filesystem->shouldHaveReceived('ensureDirectoryExists')
            ->with(Hyde::path('resources/views/vendor/hyde/layouts'))
            ->twice();

        $this->filesystem->shouldHaveReceived('copy')->with(
            Hyde::path('packages/framework/resources/views/layouts/app.blade.php'),
            Hyde::path('resources/views/vendor/hyde/layouts/app.blade.php')
        )->once();

        $this->filesystem->shouldHaveReceived('copy')->with(
            Hyde::path('packages/framework/resources/views/layouts/page.blade.php'),
            Hyde::path('resources/views/vendor/hyde/layouts/page.blade.php')
        )->once();
    }

    public function testFormatOutput()
    {
        $helper = new InteractivePublishCommandHelper('hyde-layouts');

        $output = $helper->formatOutput([
            'resources/views/vendor/hyde/layouts/app.blade.php',
            'resources/views/vendor/hyde/layouts/page.blade.php',
            'resources/views/vendor/hyde/layouts/post.blade.php',
        ]);

        $this->assertSame('Published files [app.blade.php, page.blade.php, post.blade.php]', $output);
    }

    public function testFormatOutputWithSingleFile()
    {
        $output = $this->getHelper()->formatOutput($this->selectedFiles(1));

        $this->assertSame('Published file [app.blade.php]', $output);
    }

    public function testFormatOutputWithMultipleFiles()
    {
        $output = $this->getHelper()->formatOutput($this->selectedFiles(3));

        $this->assertSame('Published files [app.blade.php, docs.blade.php, footer.blade.php]', $output);
    }

    public function testFormatOutputWithManyFiles()
    {
        $output = $this->getHelper()->formatOutput($this->selectedFiles(7));

        $this->assertSame('Published files [app.blade.php, docs.blade.php, footer.blade.php] and 4 more', $output);
    }

    public function testFormatOutputWithAllFiles()
    {
        $output = $this->getHelper()->mockPublishableFileCount(10)->formatOutput($this->selectedFiles(10));

        $this->assertSame('Published all files, including [app.blade.php, docs.blade.php, footer.blade.php] and 7 more', $output);
    }

    protected function getHelper(): MockableInteractivePublishCommandHelper
    {
        return new MockableInteractivePublishCommandHelper('hyde-layouts');
    }

    protected function selectedFiles(int $take = 10): array
    {
        $files = [
            'resources/views/vendor/hyde/layouts/app.blade.php',
            'resources/views/vendor/hyde/layouts/docs.blade.php',
            'resources/views/vendor/hyde/layouts/footer.blade.php',
            'resources/views/vendor/hyde/layouts/head.blade.php',
            'resources/views/vendor/hyde/layouts/meta.blade.php',
            'resources/views/vendor/hyde/layouts/navigation.blade.php',
            'resources/views/vendor/hyde/layouts/page.blade.php',
            'resources/views/vendor/hyde/layouts/post.blade.php',
            'resources/views/vendor/hyde/layouts/scripts.blade.php',
            'resources/views/vendor/hyde/layouts/styles.blade.php',
        ];

        return array_slice($files, 0, $take);
    }
}

class MockableInteractivePublishCommandHelper extends InteractivePublishCommandHelper
{
    public ?int $mockedPublishableFilesMapCount = null;

    public function mockPublishableFileCount(int $count): self
    {
        $this->mockedPublishableFilesMapCount = $count;

        return $this;
    }

    protected function publishableFilesMapCount(): int
    {
        return $this->mockedPublishableFilesMapCount ?? parent::publishableFilesMapCount();
    }
}