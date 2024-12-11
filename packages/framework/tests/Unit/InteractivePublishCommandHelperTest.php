<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Console\Helpers\InteractivePublishCommandHelper
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\PublishViewsCommandTest
 */
class InteractivePublishCommandHelperTest extends UnitTestCase
{
    /** @var \Illuminate\Filesystem\Filesystem&\Mockery\MockInterface */
    protected $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = $this->mockFilesystemStrict();
    }

    protected function tearDown(): void
    {
        $this->verifyMockeryExpectations();
    }
}
