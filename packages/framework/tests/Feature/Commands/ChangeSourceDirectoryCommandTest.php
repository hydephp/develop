<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

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

        $this->assertStringContainsString("'source_root' => 'test',",
            file_get_contents(Hyde::path('config/hyde.php'))
        );

        Filesystem::moveDirectory('test/_pages', '_pages');
        Filesystem::moveDirectory('test/_posts', '_posts');
        Filesystem::moveDirectory('test/_docs', '_docs');

        $config = Filesystem::getContents('config/hyde.php');
        $config = str_replace("'source_root' => 'test',", "'source_root' => '',", $config);
        Filesystem::putContents('config/hyde.php', $config);
    }
}
