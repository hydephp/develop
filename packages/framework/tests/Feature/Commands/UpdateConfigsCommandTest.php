<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Console\Commands\UpdateConfigsCommand
 */
class UpdateConfigsCommandTest extends TestCase
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

    public function test_command_has_expected_output()
    {
        $this->artisan('update:configs')
            ->expectsOutput('Published config files to '.Hyde::path('config'))
            ->assertExitCode(0);
    }

    public function test_config_files_are_published()
    {
        $this->assertDirectoryDoesNotExist(Hyde::path('config'));

        $this->artisan('update:configs')->assertExitCode(0);

        $this->assertFileEquals(Hyde::vendorPath('config/hyde.php'), Hyde::path('config/hyde.php'));

        $this->assertDirectoryExists(Hyde::path('config'));
    }

    public function test_command_overwrites_existing_files()
    {
        File::makeDirectory(Hyde::path('config'));
        File::put(Hyde::path('config/hyde.php'), 'foo');

        $this->artisan('update:configs')->assertExitCode(0);

        $this->assertNotEquals('foo', File::get(Hyde::path('config/hyde.php')));
    }
}
