<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Testing\TestCase;
use Hyde\Hyde;

/**
 * @covers \Hyde\Console\Commands\ChangeSourceDirectoryCommand
 */
class ChangeSourceDirectoryCommandTest extends TestCase
{
    public function test_command_moves_source_directories_to_new_supplied_directory_and_updates_the_configuration_file()
    {
        $this->file('_pages/tracker.txt', 'This should be moved to the new location');

        $this->artisan('change:sourceDirectory test')
            ->expectsOutput('Setting [test] as the project source directory!')
            ->expectsOutput('Creating directory')
            ->expectsOutput('Moving source directories')
            ->expectsOutput('Updating configuration file')
            ->expectsOutput('All done!')
            ->assertExitCode(0);

        $this->assertDirectoryDoesNotExist(Hyde::path('_pages'));
        $this->assertDirectoryDoesNotExist(Hyde::path('_posts'));
        $this->assertDirectoryDoesNotExist(Hyde::path('_docs'));

        $this->assertDirectoryExists(Hyde::path('test/_pages'));
        $this->assertDirectoryExists(Hyde::path('test/_posts'));
        $this->assertDirectoryExists(Hyde::path('test/_docs'));

        $this->assertFileExists(Hyde::path('test/_pages/tracker.txt'));
        $this->assertSame('This should be moved to the new location',
            file_get_contents(Hyde::path('test/_pages/tracker.txt'))
        );
    }
}
