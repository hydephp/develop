<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Console\Commands\PublishConfigsCommand
 */
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

    public function testCommandHasExpectedOutput()
    {
        $this->artisan('publish:configs')
            ->expectsChoice('Which configuration files do you want to publish?', 'All configs', $this->expectedOptions())
            ->expectsOutput(sprintf('Published config files to [%s]', Hyde::path('config')))
            ->assertExitCode(0);
    }

    public function testConfigFilesArePublished()
    {
        $this->assertDirectoryDoesNotExist(Hyde::path('config'));

        $this->artisan('publish:configs')
            ->expectsChoice('Which configuration files do you want to publish?', 'All configs', $this->expectedOptions())
            ->assertExitCode(0);

        $this->assertFileEquals(Hyde::vendorPath('config/hyde.php'), Hyde::path('config/hyde.php'));

        $this->assertDirectoryExists(Hyde::path('config'));
    }

    public function testCommandOverwritesExistingFiles()
    {
        File::makeDirectory(Hyde::path('config'));
        File::put(Hyde::path('config/hyde.php'), 'foo');

        $this->artisan('publish:configs')
            ->expectsChoice('Which configuration files do you want to publish?', 'All configs', $this->expectedOptions())
            ->assertExitCode(0);

        $this->assertNotEquals('foo', File::get(Hyde::path('config/hyde.php')));
    }

    protected function expectedOptions(): array
    {
        return [
            'All configs',
            '<comment>hyde-configs</comment>: Main configuration files',
            '<comment>support-configs</comment>: Laravel and package configuration files',
        ];
    }
}
