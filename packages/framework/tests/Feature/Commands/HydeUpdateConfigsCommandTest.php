<?php

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Framework\Actions\ChecksIfConfigIsUpToDate;
use Hyde\Framework\Commands\HydeUpdateConfigsCommand;
use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Framework\Commands\HydeUpdateConfigsCommand
 */
class HydeUpdateConfigsCommandTest extends TestCase
{
    /** Setup */
    public function setUp(): void
    {
        parent::setUp();

        ChecksIfConfigIsUpToDate::$isUpToDate = null;

        backupDirectory(Hyde::path('config'));
        deleteDirectory(Hyde::path('config'));
    }

    /** @test */
    public function test_command_has_expected_output()
    {
        $this->artisan('update:configs')
            ->expectsOutput('Published config files to '.Hyde::path('config'))
            ->assertExitCode(0);
    }

    /** @test */
    public function test_config_files_are_published()
    {
        $this->assertDirectoryDoesNotExist(Hyde::path('config'));

        $this->artisan('update:configs')
            ->assertExitCode(0);

        $this->assertFileEquals(Hyde::vendorPath('config/hyde.php'), Hyde::path('config/hyde.php'));

        $this->assertDirectoryExists(Hyde::path('config'));
    }

    /** @test */
    public function test_command_overwrites_existing_files()
    {
        File::makeDirectory(Hyde::path('config'));
        File::put(Hyde::path('config/hyde.php'), 'foo');

        $this->artisan('update:configs')
            ->assertExitCode(0);

        $this->assertNotEquals('foo', File::get(Hyde::path('config/hyde.php')));
    }

    /** @test */
    public function test_command_description_warns_when_out_of_date()
    {
        backup(Hyde::path('config/hyde.php'));
        $this->artisan('update:configs');
        $this->assertStringNotContainsString('Your configuration may be out of date',
            (new HydeUpdateConfigsCommand)->getDescription());

        file_put_contents(Hyde::path('config/hyde.php'), str_replace(
            '--------------------------------------------------------------------------',
           '', file_get_contents(
            Hyde::path('config/hyde.php')
        )));

        ChecksIfConfigIsUpToDate::$isUpToDate = null;

        $this->assertStringContainsString('Your configuration may be out of date',
            (new HydeUpdateConfigsCommand)->getDescription());

        restore(Hyde::path('config/hyde.php'));
    }

    /** Teardown */
    public function tearDown(): void
    {
        restoreDirectory(Hyde::path('config'));

        parent::tearDown();
    }
}
