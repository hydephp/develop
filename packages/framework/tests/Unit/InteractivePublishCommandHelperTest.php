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
}
