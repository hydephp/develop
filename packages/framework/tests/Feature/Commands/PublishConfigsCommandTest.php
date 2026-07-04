<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Step 7 (§8): publish:configs is now a thin, deprecated delegator. It prints a one-line
 * deprecation notice and forwards to `php hyde vendor:publish --tag=hyde-config`, which
 * publishes exactly the six Hyde-owned config files (asserted by the Step 6 provider test).
 */
#[CoversClass(\Hyde\Console\Commands\PublishConfigsCommand::class)]
class PublishConfigsCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Filesystem::copyDirectory('config', 'config-bak');
        Filesystem::deleteDirectory('config');
    }

    public function tearDown(): void
    {
        Filesystem::moveDirectory('config-bak', 'config', true);
        Filesystem::deleteDirectory('config-bak');

        parent::tearDown();
    }

    public function testPrintsNoticeAndDelegatesToVendorPublishHydeConfigTag()
    {
        $this->assertDirectoryDoesNotExist(Hyde::path('config'));

        $this->artisan('publish:configs --no-interaction')
            ->expectsOutputToContain('publish:configs is deprecated. Use php hyde vendor:publish --tag=hyde-config instead.')
            ->assertExitCode(0);

        // The hyde-config tag publishes exactly the six Hyde-owned configs.
        $this->assertFileExists(Hyde::path('config/hyde.php'));
        $this->assertFileExists(Hyde::path('config/docs.php'));
        $this->assertFileExists(Hyde::path('config/markdown.php'));
        $this->assertFileExists(Hyde::path('config/view.php'));
        $this->assertFileExists(Hyde::path('config/cache.php'));
        $this->assertFileExists(Hyde::path('config/commands.php'));

        // Torchlight is obtained via its own package tag, never through hyde-config.
        $this->assertFileDoesNotExist(Hyde::path('config/torchlight.php'));
    }
}
