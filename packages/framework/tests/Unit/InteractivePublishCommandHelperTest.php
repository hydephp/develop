<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\Providers\ViewServiceProvider;
use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Blade;
use Mockery;

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

        Blade::partialMock()->shouldReceive('component');

        $app = $this->setupMockApplication();

        (new ViewServiceProvider($app))->boot();
    }

    protected function tearDown(): void
    {
        $this->verifyMockeryExpectations();

        Container::setInstance($this->originalApp);
    }

    protected function setupMockApplication(): Container
    {
        $this->originalApp = Container::getInstance();

        $app = Mockery::mock(app())->makePartial();
        $app->shouldReceive('resourcePath')->andReturn(Hyde::path('resources'));

        Container::setInstance($app);

        return $app;
    }
}
