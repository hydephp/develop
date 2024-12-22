<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Console\Helpers\InteractivePublishCommandHelper;
use Hyde\Foundation\Providers\ViewServiceProvider;
use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\File;
use Mockery;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @covers \Hyde\Console\Helpers\InteractivePublishCommandHelper
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\PublishViewsCommandTest
 */
class InteractivePublishCommandHelperTest extends UnitTestCase
{
    protected static bool $needsKernel = true;

    /** @var \Illuminate\Filesystem\Filesystem&\Mockery\MockInterface */
    protected $filesystem;

    protected $originalApp;

    protected function setUp(): void
    {
        $this->filesystem = $this->mockFilesystemStrict();

        File::swap($this->filesystem);
        Blade::partialMock()->shouldReceive('component');

        $app = $this->setupMockApplication();

        (new ViewServiceProvider($app))->boot();
    }

    protected function tearDown(): void
    {
        $this->verifyMockeryExpectations();

        Container::setInstance($this->originalApp);
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
            "resources/views/vendor/hyde/layouts/app.blade.php",
            "resources/views/vendor/hyde/layouts/page.blade.php",
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

    protected function setupMockApplication(): Container
    {
        $this->originalApp = Container::getInstance();

        $app = Mockery::mock(app())->makePartial();
        $app->shouldReceive('resourcePath')->andReturnUsing(fn (string $path) => "resources/$path");

        Container::setInstance($app);

        return $app;
    }
}
