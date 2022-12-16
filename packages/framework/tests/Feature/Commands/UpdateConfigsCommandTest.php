<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;
use function is_dir;

/**
 * @covers \Hyde\Console\Commands\UpdateConfigsCommand
 */
class UpdateConfigsCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->backupDirectory(Hyde::path('config'));
        $this->deleteDirectory(Hyde::path('config'));
    }

    public function tearDown(): void
    {
        $this->restoreDirectory(Hyde::path('config'));

        parent::tearDown();
    }

    protected function backupDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            File::copyDirectory($directory, $directory.'-bak');
        }
    }

    protected function restoreDirectory(string $directory): void
    {
        if (is_dir($directory.'-bak')) {
            File::moveDirectory($directory.'-bak', $directory, true);
            File::deleteDirectory($directory.'-bak');
        }
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
