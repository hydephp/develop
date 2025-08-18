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
    public function testCommandMovesSourceDirectoriesToNewSuppliedDirectoryAndUpdatesTheConfigurationFile()
    {
        $this->file('_pages/tracker.txt', 'This should be moved to the new location');

        $this->artisan('change:sourceDirectory test')
            ->expectsOutput('Setting [test] as the project source directory!')
            ->expectsOutput(' > Creating directory')
            ->expectsOutput(' > Moving source directories')
            ->expectsOutput(' > Updating configuration file')
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

    public function testWithMissingConfigSearchString()
    {
        $this->file('_pages/tracker.txt', 'This should be moved to the new location');

        $config = Filesystem::getContents('config/hyde.php');
        $config = str_replace("'source_root' => '',", "'no_source_root' => '',", $config);
        Filesystem::putContents('config/hyde.php', $config);

        $this->artisan('change:sourceDirectory test')
            ->expectsOutput('Setting [test] as the project source directory!')
            ->expectsOutput(' > Creating directory')
            ->expectsOutput(' > Moving source directories')
            ->expectsOutput(' > Updating configuration file')
            ->expectsOutput("Warning: Automatic configuration update failed, to finalize the change, please set the `source_root` setting to 'test' in `config/hyde.php`")
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

        Filesystem::moveDirectory('test/_pages', '_pages');
        Filesystem::moveDirectory('test/_posts', '_posts');
        Filesystem::moveDirectory('test/_docs', '_docs');

        $config = Filesystem::getContents('config/hyde.php');
        $config = str_replace("'no_source_root' => '',", "'source_root' => '',", $config);
        Filesystem::putContents('config/hyde.php', $config);
    }

    public function testWithNameMatchingCurrentValue()
    {
        $this->artisan('change:sourceDirectory /')
            ->expectsOutput("The directory '/' is already set as the project source root!")
            ->assertExitCode(409);
    }

    public function testWithExistingDirectory()
    {
        $this->directory('test');
        $this->directory('test/_pages');
        $this->file('test/_pages/foo');

        $this->artisan('change:sourceDirectory test')
            ->expectsOutput('Directory already exists!')
            ->assertExitCode(409);
    }

    public function testWithTargetContainingSubdirectoryFile()
    {
        $this->directory('test');
        $this->file('test/_pages');

        $this->artisan('change:sourceDirectory test')
            ->expectsOutput('Directory already exists!')
            ->assertExitCode(409);
    }

    public function testWithTargetBeingFile()
    {
        $this->file('test');

        $this->artisan('change:sourceDirectory test')
            ->expectsOutput('A file already exists at this path!')
            ->assertExitCode(409);
    }
}
