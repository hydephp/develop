<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\ChangeSourceDirectoryCommand
 */
class ChangeSourceDirectoryCommandTest extends TestCase
{
    public function test_command_moves_source_directories_to_new_supplied_directory_and_updates_the_configuration_file()
    {
        $this->artisan('change:sourceDirectory test')
            ->expectsOutput('Setting [test] as the project source directory!')
            ->expectsOutput('Creating directory')
            ->expectsOutput('Moving source directories')
            ->expectsOutput('Updating configuration file')
            ->expectsOutput('All done!')
            ->assertExitCode(0);
    }
}
